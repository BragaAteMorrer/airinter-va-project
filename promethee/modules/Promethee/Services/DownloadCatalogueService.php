<?php
namespace Modules\Promethee\Services;

use App\Models\{Aircraft, Airport, File, Subfleet};

class DownloadCatalogueService
{
public function category(File $file): string {
       $reference = strtolower((string) $file->ref_model);
       $search = strtolower(implode(' ', [$file->name, $file->description, $file->path, $reference]));
       if (str_contains($reference, 'promethee\\download\\')) {
           return strtolower((string) preg_replace('/^.*\\\\/', '', $file->ref_model)) ?: 'documents';
       }
       if (str_contains($search, 'acars')) return 'acars';
       if (str_contains($reference, 'aircraft') || str_contains($reference, 'subfleet')) return 'fleet';
       if (str_contains($reference, 'airport')) return 'airports';
       return 'documents';
   }

public function subcategory(File $file): string {
       $category = $this->category($file);
       $subcategory = trim((string) $file->ref_model_id);
       // Downloads created before subcategories used the category as their ID.
       return $subcategory === '' || strtolower($subcategory) === $category ? 'Général' : $subcategory;
   }

public function groups() {
       $files = File::query()->orderBy('name')->get();

       // The files table is shared with phpVMS. Keep every resource that is
       // explicitly owned by Prométhée, linked to the operational catalogue,
       // or looks like a legacy standalone download. Unknown legacy entries
       // are deliberately kept in an "uncategorized" bucket so admins can
       // recover/reclassify them instead of making them invisible.
       $knownModels = [Aircraft::class, Subfleet::class, Airport::class];
       $files = $files->filter(function (File $file) use ($knownModels) {
           $reference = trim((string) $file->ref_model);
           if (str_starts_with($reference, 'Modules\\Promethee\\Download\\')) return true;
           if (in_array($reference, $knownModels, true)) return true;
           if ($reference === '') return true;

           return false;
       });

       return $files->groupBy(function (File $file) {
           $category = $this->category($file);
           return in_array($category, ['acars', 'fleet', 'airports', 'documents'], true)
               ? $category
               : 'uncategorized';
       });
   }
}
