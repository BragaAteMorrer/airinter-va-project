<?php
namespace Modules\Promethee\Console;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class LocalUserCommand extends Command
{
    protected $signature = 'promethee:local-user';
    protected $description = 'Crée un compte uniquement dans la copie Docker locale.';
    public function handle(): int
    {
        if (!app()->environment('local') || !filter_var(env('PROMETHEE_LOCAL'), FILTER_VALIDATE_BOOL)) return self::FAILURE;
        $email='admin@promethee.test';
        $password=env('PROMETHEE_LOCAL_PASSWORD', 'promethee-local');
        $airline=DB::table('airlines')->first();
        $rank=DB::table('ranks')->first();
        $user = DB::table('users')->where('email', $email)->first();
        if ($user) {
            $id = $user->id;
            DB::table('users')->where('id', $id)->update([
                'name'=>'Équipe Prométhée','password'=>Hash::make($password),
                'state'=>1,'status'=>0,'email_verified_at'=>now(),'updated_at'=>now(),
            ]);
        } else {
            $id=DB::table('users')->insertGetId([
                'email'=>$email,'name'=>'Équipe Prométhée','password'=>Hash::make($password),
                'api_key'=>Str::random(40),'airline_id'=>$airline->id,'rank_id'=>$rank?->id,
                'state'=>1,'status'=>0,'timezone'=>'Europe/Paris','country'=>'FR',
                'email_verified_at'=>now(),'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
        $role=DB::table('roles')->where('name','admin')->first();
        if ($role && !DB::table('role_user')->where(['role_id'=>$role->id,'user_id'=>$id,'user_type'=>'App\\Models\\User'])->exists()) {
            DB::table('role_user')->insert(['role_id'=>$role->id,'user_id'=>$id,'user_type'=>'App\\Models\\User']);
        }
        $apiKey = DB::table('users')->where('id', $id)->value('api_key');
        $this->info('Connexion locale : '.$email.' / '.$password);
        $this->info('Clé API locale : '.$apiKey);
        return self::SUCCESS;
    }
}
