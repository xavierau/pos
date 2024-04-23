<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use \Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Config;
use Zip;
use Illuminate\Support\Facades\Storage;

class ModuleSettingsController extends Controller
{

     /*
    * Return current version (as plain text).
    */
    public function getCurrentVersion($module_name){
        $version = File::get(base_path()."/Modules/$module_name/version.txt");
        return $version;
    }

     /*
    * Check if a new Update exist.
    */
    public function check($module_name)
    {
        $lastVersionInfo = $this->getLastVersion($module_name);
        if( version_compare($lastVersionInfo['version'], $this->getCurrentVersion($module_name), ">") )
            return $lastVersionInfo['version'];

        return '';
    }

    private function getLastVersion($module_name){
        $lowercaseName = strtolower($module_name);
        $content = file_get_contents(config($lowercaseName . '.update_baseurl').'/laraupdater.json');
        $content = json_decode($content, true);
        return $content; //['version' => $v, 'archive' => 'RELEASE-$v.zip', 'description' => 'plain text...'];
    }

    //-------------- get_modules_info ---------------\\

    public function get_modules_info(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'module_settings', Setting::class);
        $allModules = Module::all();
        $ModulesInstalled = [];
        foreach($allModules as $key => $module_name){
            $lowercaseName = strtolower($module_name);
            $item['module_name'] = $key;
            $item['current_version'] = $this->getCurrentVersion($key);
            $item['status'] = \Module::collections()->has($key);
            $item['description'] = config($lowercaseName . '.description');
           
            $ModulesInstalled[] = $item;

        }

        return response()->json($ModulesInstalled);
    }

    public function update_status_module(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'module_settings', Setting::class);
        $module = Module::find($request->name);
        ($request->status === true) ? $module->enable() : $module->disable();

        return response()->json(['success' => true]);
    }

    public function update_database_module(Request $request , $module_name)
    {

        try {
            $module = Module::find($module_name);
            if($module){
                Artisan::call('migrate', ['--force' => true, '--path' => 'Modules/'. $module_name .'/database/migrations']);
                Artisan::call('module:publish');
                Artisan::call('optimize:clear');

                return response()->json(['success' => true , 'message' => 'Database Updated !!'] , 200);

            }else{
                return response()->json(['success' => false , 'message' => 'Module Name Not exist!!'], 400);

            }

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


    public function upload_module(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'module_settings', Setting::class);
        ini_set('max_execution_time', 600); //600 seconds = 10 minutes 
            
        try {

            $zip_path = $request->module_zip;
            $OriginalName = $zip_path->getClientOriginalName();
            $zip_temp_path = $zip_path->path();
            $zip = Zip::open($zip_temp_path);
            if(str_contains($OriginalName , 'codecanyon-')){
                $zip_name = $this->Unzip($zip);
            }else{
                $zip_name = $zip_path->getClientOriginalName();
                $zip->extract(storage_path() . '/app/public/Modules');
            }
                
            $module_name = str_replace('.zip', '', $zip_name);

            File::moveDirectory(storage_path() . '/app/public/Modules/' . $module_name, base_path() . '/Modules/' . $module_name, true);
            File::deleteDirectory(storage_path() . '/app/public/Modules');
            
            $module = Module::find($module_name);
            $module->enable();
            Artisan::call('config:clear');
            
            try {
                Artisan::call('migrate', ['--force' => true, '--path' => 'Modules/'. $module_name .'/Database/Migrations']);

                $role = Role::findOrFail(1);
            
                $permissions = array(
                    0 => 'online_store',
                );
                        
                foreach ($permissions as $permission_name) {
                    $perm = Permission::firstOrCreate(['name' => $permission_name]);
                        $role->givePermissionTo($perm);
                }

                Artisan::call('optimize:clear');
                Artisan::call('module:publish');
                
            } catch (\Exception $e) {
                \Log::error("Migration error: " . $e->getMessage());
            }
          
            

        } catch (\Exception $e) {

            return $e->getMessage();
            
            return 'Something went wrong';
        }

    }

    private function getZipName($zip_path){
       $array = explode('\\', $zip_path);
        return end($array);
    }

    private function Unzip($zip_file){
        $Path_Code_Canyon = storage_path() . '/app/public/Modules';
        $zip_file->extract($Path_Code_Canyon);
        $files = File::allfiles($Path_Code_Canyon);

        foreach($files as $file){
            if(strpos($file->getRelativePathname(), '.zip') !== false){
                $filePath = $file->getRelativePathname();
                $zip = Zip::open($Path_Code_Canyon . '/' . $filePath);
                $zip->extract(storage_path() . '/app/public/Modules');
                $zipName = $this->getZipName($filePath);
                return $zipName;
            }
        }

        return false;

    }






}



  