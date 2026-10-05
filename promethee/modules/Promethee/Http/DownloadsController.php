<?php
namespace Modules\Promethee\Http;
use App\Models\{Aircraft,Airline,Airport,Bid,File,Flight,Pirep,SimBrief,User,Fare,Subfleet,Rank};
use App\Models\Enums\{AircraftState,AircraftStatus,FareType,FlightType,PirepState,PirepStatus,UserState};
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File as Filesystem;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Services\AirportService;
use App\Services\FinanceService;
use App\Services\FileService;
use App\Services\UserService;
use App\Support\Money;
use App\Support\Countries;
use Modules\Promethee\Services\{AirframeMaintenanceService,BrandingService,BulletinService,CompanyAccessService,DemandProfileService,EconomyFareResolver,EconomyService,EngineMaintenanceService,FleetRotationService,FlightOpsService,LegacyPirepScoringService,OperationalWeatherService,PilotPirepDeletionService,PirepJournalService,RegionalOperationsService,SafetyAnalyzer};


class DownloadsController extends PrometheeWebController
{
public function downloads(Request $r) {
       return $this->page('downloads', ['groups' => app(\Modules\Promethee\Services\DownloadCatalogueService::class)->groups()]);
   }

public function documents() {
       return $this->downloadCategoryPage('documents');
   }

public function myDocuments(Request $r, UserService $users) {
       $pilot = $r->user();
       $allowedSubfleets = $users->getAllowableSubfleets($pilot);
       $aircraftTypes = $allowedSubfleets->pluck('type')->filter()->map(fn ($type) => strtoupper(trim((string) $type)))->unique()->sort()->values();

       $documents = app(\Modules\Promethee\Services\DownloadCatalogueService::class)->groups()->get('documents', collect())->map(function (File $file) {
           $subcategory = app(\Modules\Promethee\Services\DownloadCatalogueService::class)->subcategory($file);
           $parts = array_map('trim', explode('·', $subcategory, 2));
           $file->setAttribute('promethee_document_section', $parts[0] ?: 'Général');
           $file->setAttribute('promethee_aircraft_type', strtoupper($parts[1] ?? ''));
           $file->setAttribute('promethee_extension', strtoupper(pathinfo(parse_url((string) $file->path, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION)));
           return $file;
       });

       $personalDocuments = $documents->filter(function (File $file) use ($aircraftTypes) {
           $type = (string) $file->promethee_aircraft_type;
           return $type === '' || $aircraftTypes->contains($type);
       })->values();

       $sections = $personalDocuments->groupBy(fn (File $file) => $file->promethee_document_section);

       return $this->page('my-documents', [
           'pilot' => $pilot,
           'aircraftTypes' => $aircraftTypes,
           'documents' => $personalDocuments,
           'sections' => $sections,
           'allDocumentsCount' => $documents->count(),
       ]);
   }

public function document(string $file) {
       $asset = File::findOrFail($file);
       abort_unless(app(\Modules\Promethee\Services\DownloadCatalogueService::class)->category($asset) === 'documents' && $this->isManagedDownload($asset), 404);

       $urlPath = parse_url((string) $asset->path, PHP_URL_PATH) ?: '';
       $extension = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION));
       $officeExtensions = ['ppt', 'pptx', 'doc', 'docx', 'xls', 'xlsx'];
       $previewType = match (true) {
           $extension === 'pdf' => 'pdf',
           in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true) => 'image',
           in_array($extension, $officeExtensions, true) => 'office',
           in_array($extension, ['txt', 'md', 'csv'], true) => 'text',
           default => 'none',
       };

       return $this->page('document-viewer', compact('asset', 'extension', 'previewType'));
   }

public function documentContent(string $file) {
       $asset = File::findOrFail($file);
       abort_unless(app(\Modules\Promethee\Services\DownloadCatalogueService::class)->category($asset) === 'documents' && $this->isManagedDownload($asset), 404);

       if ($asset->isExternalFile) {
           return redirect()->away($asset->url);
       }

       $disk = $asset->disk ?? config('filesystems.public_files');
       abort_unless(\Illuminate\Support\Facades\Storage::disk($disk)->exists($asset->path), 404);

       $path = \Illuminate\Support\Facades\Storage::disk($disk)->path($asset->path);
       $mime = mime_content_type($path) ?: 'application/octet-stream';

       return response()->file($path, [
           'Content-Type' => $mime,
           'Content-Disposition' => 'inline; filename="'.addslashes($asset->filename).'"',
           'X-Content-Type-Options' => 'nosniff',
           'Cache-Control' => 'private, max-age=300',
       ]);
   }

public function downloadCategoryPage(string $category) {
       $sections = ['acars' => ['ACARS', 'Clients et documentation de connexion'], 'fleet' => ['Avions et flotte', 'Livrées, appareils et documents associés'], 'airports' => ['Aéroports et HUBs', 'Scènes, cartes et ressources réseau'], 'documents' => ['Documentation interne', 'Procédures, carrière, formation et documentation par type d’avion']];
       abort_unless(array_key_exists($category, $sections), 404);
       $files = app(\Modules\Promethee\Services\DownloadCatalogueService::class)->groups()->get($category, collect())->groupBy(fn (File $file) => app(\Modules\Promethee\Services\DownloadCatalogueService::class)->subcategory($file));
       return $this->page('download-category', compact('category', 'sections', 'files'));
   }

public function download(string $file) {
       $allowed = app(\Modules\Promethee\Services\DownloadCatalogueService::class)->groups()->flatten(1)->contains(fn (File $asset) => (string) $asset->id === $file);
       abort_unless($allowed, 404);

       return app(\App\Http\Controllers\Frontend\DownloadController::class)->show($file);
   }

public function adminDownloads() {
       return $this->page('admin.downloads', [
           'groups' => app(\Modules\Promethee\Services\DownloadCatalogueService::class)->groups(),
           'aircraftTypes' => Subfleet::query()->pluck('type')->filter()->unique()->sort()->values(),
       ]);
   }

public function storeDownload(Request $r, FileService $files) {
       $data = $r->validate([
           'name' => 'required|string|max:120', 'description' => 'nullable|string|max:1000',
           'category' => 'required|in:acars,fleet,airports,documents', 'subcategory' => 'nullable|string|max:80',
           'document_section' => 'nullable|in:general,operations,career,training,aircraft,regulations,forms',
           'aircraft_type' => 'nullable|string|max:30', 'file' => 'nullable|file|max:102400',
           'url' => 'nullable|url|max:2000', 'public' => 'nullable|boolean',
       ]);
       if (!$r->hasFile('file') && empty($data['url'])) return back()->withErrors(['url' => 'Ajoutez un fichier ou une URL.'])->withInput();
       $documentSections = [
           'general' => 'Général', 'operations' => 'Opérations', 'career' => 'Carrière',
           'training' => 'Formation', 'aircraft' => 'Documentation avion',
           'regulations' => 'Réglementation', 'forms' => 'Formulaires',
       ];
       $subcategory = trim($data['subcategory'] ?? '');
       if ($data['category'] === 'documents') {
           $section = $documentSections[$data['document_section'] ?? 'general'] ?? 'Général';
           $aircraftType = strtoupper(trim($data['aircraft_type'] ?? ''));
           $subcategory = $section.($aircraftType !== '' ? ' · '.$aircraftType : '');
       }
       $attributes = [
           'name' => $data['name'], 'description' => $data['description'] ?? '', 'public' => $r->boolean('public'),
           'ref_model' => 'Modules\\Promethee\\Download\\'.ucfirst($data['category']),
           'ref_model_id' => $subcategory !== '' ? $subcategory : $data['category'],
       ];
       if ($r->hasFile('file')) $files->saveFile($r->file('file'), 'promethee-downloads', $attributes);
       else { $asset = new File($attributes); $asset->id = File::createNewHashId(); $asset->path = $data['url']; $asset->save(); }
       return back()->with('success', 'Téléchargement enregistré.');
   }

private function isManagedDownload(File $asset): bool {
       // Everything surfaced by the Prométhée download centre is manageable
       // from this screen. On first edit, legacy phpVMS catalogue attachments
       // are adopted by Prométhée and become ordinary download resources.
       return app(\Modules\Promethee\Services\DownloadCatalogueService::class)->groups()->flatten(1)
           ->contains(fn (File $download) => (string) $download->id === (string) $asset->id);
   }

public function editDownload(string $file) {
       $asset = File::findOrFail($file);
       abort_unless($this->isManagedDownload($asset), 403);
       $aircraftTypes = Subfleet::query()->pluck('type')->filter()->unique()->sort()->values();
       return $this->page('admin.edit-download', compact('asset', 'aircraftTypes'));
   }

public function updateDownload(Request $r, string $file, FileService $files) {
       $asset = File::findOrFail($file);
       abort_unless($this->isManagedDownload($asset), 403);

       $data = $r->validate([
           'name' => 'required|string|max:120', 'description' => 'nullable|string|max:1000',
           'category' => 'required|in:acars,fleet,airports,documents', 'subcategory' => 'nullable|string|max:80',
           'document_section' => 'nullable|in:general,operations,career,training,aircraft,regulations,forms',
           'aircraft_type' => 'nullable|string|max:30',
           'file' => 'nullable|file|max:102400', 'url' => 'nullable|url|max:2000', 'public' => 'nullable|boolean',
       ]);
       $documentSections = [
           'general' => 'Général', 'operations' => 'Opérations', 'career' => 'Carrière',
           'training' => 'Formation', 'aircraft' => 'Documentation avion',
           'regulations' => 'Réglementation', 'forms' => 'Formulaires',
       ];
       $subcategory = trim($data['subcategory'] ?? '');
       if ($data['category'] === 'documents') {
           $section = $documentSections[$data['document_section'] ?? 'general'] ?? 'Général';
           $aircraftType = strtoupper(trim($data['aircraft_type'] ?? ''));
           $subcategory = $section.($aircraftType !== '' ? ' · '.$aircraftType : '');
       }
       $attributes = [
           'name' => $data['name'], 'description' => $data['description'] ?? '', 'public' => $r->boolean('public'),
           'ref_model' => 'Modules\\Promethee\\Download\\'.ucfirst($data['category']),
           'ref_model_id' => $subcategory !== '' ? $subcategory : $data['category'],
       ];

       if ($r->hasFile('file')) {
           // Replace the physical payload while keeping the same database ID.
           // This preserves download counters and existing Prométhée links.
           $oldPath = (string) $asset->path;
           $oldDisk = $asset->disk ?? config('filesystems.public_files');
           $replacement = $files->saveFile($r->file('file'), 'promethee-downloads', $attributes);
           $asset->fill($attributes);
           $asset->path = $replacement->path;
           $asset->disk = $replacement->disk;
           $asset->save();
           $replacement->delete();
           if ($oldPath !== '' && !str_starts_with($oldPath, 'http') && $oldPath !== $asset->path) {
               \Illuminate\Support\Facades\Storage::disk($oldDisk)->delete($oldPath);
           }
       } else {
           $asset->fill($attributes);
           if (!empty($data['url'])) {
               $oldPath = (string) $asset->path;
               $oldDisk = $asset->disk ?? config('filesystems.public_files');
               $asset->path = $data['url'];
               $asset->disk = null;
               if ($oldPath !== '' && !str_starts_with($oldPath, 'http')) {
                   \Illuminate\Support\Facades\Storage::disk($oldDisk)->delete($oldPath);
               }
           }
           $asset->save();
       }

       return redirect()->route('admin.promethee.downloads')->with('success', 'Téléchargement modifié.');
   }

public function deleteDownload(string $file, FileService $files) {
       $asset = File::findOrFail($file);
       abort_unless($this->isManagedDownload($asset), 403);
       $files->removeFile($asset);
       return back()->with('success', 'Téléchargement supprimé.');
   }
}
