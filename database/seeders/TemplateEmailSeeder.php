<?php

namespace Database\Seeders;

use App\Models\Email;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class TemplateEmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $footer = '';

        $templates = [
            [
                'name' => 'Forgot Password',
                'description' => 'Template Forgot Password',
                'template' => "<h1>Bonjour!</h1>
                            <p><span>Vous recevez cet e-mail car nous avons reçu une demande de réinitialisation du mot de passe pour votre compte.</span></p>
                            <br>
                            <div style='text-align:center;'><a href='{{url_reset_password}}'target='_blank'>Réinitialiser le mot de passe</a></div>
                            <br>
                            <footer>Cordialement<br>Equipe des laboratoires CETTEX </footer>    <br> " . $footer,
                'headers' =>
                '[{"header_name":"subject","header_value":"Reset Password"}]',
                'type' => 'forgot_password',
            ],
            [
                'name' => 'Activation de compte utilisateur',
                'description' => 'Template d\'Activation de compte utilisateur',
                'template' => "<p>Bonjour {{created_user_name}},</p>
                <p>Nous avons le plaisir de vous informer que votre  <strong> « Espace » </strong> a été créé avec succès.</p>
                <p> Pour pouvoir accéder à votre espace utilisateur, merci de cliquer sur le lien <a href='{{lien}}'target='_blank'> ci-dessous </a> afin de procéder à la vérification de votre compte.</p>
                {{user_credential}}
                <p>Nous vous remercions de votre confiance et restons à votre disposition pour toute question ou demande complémentaire.</p>" . $footer,
                'headers' =>
                    '[{"header_name":"subject","header_value":"Activation de compte utilisateur"}]',
                'type' => 'user_activate_account',
            ],

        ];

        if (Schema::hasTable('emails')) {
            Schema::disableForeignKeyConstraints();
            DB::table('emails')->delete();
            foreach ($templates as $template) {
                Email::create($template);
            }
            Schema::enableForeignKeyConstraints();
        }
    }
}
