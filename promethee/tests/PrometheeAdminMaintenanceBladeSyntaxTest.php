<?php

namespace Tests;

use Illuminate\Support\Facades\Blade;

final class PrometheeAdminMaintenanceBladeSyntaxTest extends TestCase
{
    public function test_admin_maintenance_blade_compiles_to_valid_php(): void
    {
        $view = base_path('modules/Promethee/Resources/views/admin-maintenance.blade.php');
        $source = file_get_contents($view);

        $this->assertNotFalse($source);

        $compiled = Blade::compileString($source);

        try {
            eval('if (false) { ?>'.$compiled.'<?php }');
        } catch (\ParseError $error) {
            $this->fail('admin-maintenance.blade.php compiled to invalid PHP: '.$error->getMessage());
        }

        $this->assertTrue(true);
    }
}
