<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('promethee_crm_mail_templates')) {
            Schema::create('promethee_crm_mail_templates', function (Blueprint $table) {
                $table->id();
                $table->string('key', 80)->unique();
                $table->string('label', 160);
                $table->string('subject', 191);
                $table->longText('body');
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        $now = now();
        $templates = [
            [
                'key' => 'pilot.pending',
                'label' => 'Candidature reçue',
                'subject' => 'Air Inter · Candidature pilote reçue',
                'body' => "Bonjour {{name}},\n\nVotre dossier pilote Air Inter a bien été transmis.\n\nIl est maintenant en attente de validation par l’équipe Air Inter VA. Vous recevrez automatiquement un nouveau message dès que votre accès Prométhée sera ouvert.\n\nStatut : DOSSIER REÇU · VALIDATION EN ATTENTE\n\nÀ très bientôt à bord,\n\nDirection de l’Exploitation Aérienne\nAir Inter Virtual Airlines",
            ],
            [
                'key' => 'pilot.welcome',
                'label' => 'Bienvenue · accès pilote ouvert',
                'subject' => 'Air Inter · Votre accès pilote est ouvert',
                'body' => "Bienvenue chez Air Inter, {{name}}\n\nVotre accès pilote est désormais ouvert.\n\nProméthée devient votre centre des opérations : réservation d’un vol Air Inter, affectation de l’appareil, préparation SimBrief, suivi Hermès et débriefing.\n\nIdentifiant pilote : {{ident}}\n\nAccès Prométhée : {{promethee_url}}\n\nBon vol et bienvenue dans la ligne,\n\nDirection de l’Exploitation Aérienne\nAir Inter Virtual Airlines",
            ],
            [
                'key' => 'pilot.rejected',
                'label' => 'Candidature refusée',
                'subject' => 'Air Inter · Mise à jour de votre candidature',
                'body' => "Bonjour {{name}},\n\nAprès examen, votre demande d’inscription Air Inter VA n’a pas été validée en l’état.\n\nSi vous pensez qu’une information manque à votre dossier ou si vous souhaitez obtenir des précisions, vous pouvez contacter l’équipe Air Inter VA.\n\nDirection de l’Exploitation Aérienne\nAir Inter Virtual Airlines",
            ],
            [
                'key' => 'admin.pilot_registered',
                'label' => 'Admin · nouvelle candidature',
                'subject' => 'Prométhée · Nouvelle candidature pilote Air Inter',
                'body' => "Une nouvelle candidature vient d’être enregistrée dans Prométhée.\n\nPilote : {{name}}\nE-mail : {{email}}\nIdentifiant : {{ident}}\nCompagnie : {{airline}}\nÉtat : {{state}}\n\nVérifiez le dossier et validez le pilote depuis l’administration.\n\nProméthée · Air Inter VA",
            ],
        ];

        foreach ($templates as $template) {
            DB::table('promethee_crm_mail_templates')->updateOrInsert(
                ['key' => $template['key']],
                $template + ['active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('promethee_crm_mail_templates');
    }
};
