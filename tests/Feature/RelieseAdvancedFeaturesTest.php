<?php

namespace Tests\Feature;

use App\Services\RelieseStudioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelieseAdvancedFeaturesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test RelieseStudioService ER diagram data generator
     */
    public function test_service_generates_er_diagram_data(): void
    {
        $service = new RelieseStudioService();
        $erData = $service->getErDiagramData();

        $this->assertArrayHasKey('tables', $erData);
        $this->assertArrayHasKey('relationships', $erData);
        $this->assertArrayHasKey('mermaid', $erData);
        $this->assertStringContainsString('erDiagram', $erData['mermaid']);
    }

    /**
     * Test RelieseStudioService model diff inspection
     */
    public function test_service_returns_model_diff_inspection_data(): void
    {
        $service = new RelieseStudioService();
        $diff = $service->getModelDiff('users');

        $this->assertArrayHasKey('table', $diff);
        $this->assertArrayHasKey('model', $diff);
        $this->assertArrayHasKey('base_code', $diff);
        $this->assertArrayHasKey('main_code', $diff);
        $this->assertEquals('users', $diff['table']);
    }

    /**
     * Test Configurator Studio Page Route
     */
    public function test_configurator_page_loads_successfully(): void
    {
        $response = $this->get('/reliese/configurator');

        $response->assertStatus(200);
        $response->assertSee('Reliese Custom Configurator Studio');
    }

    /**
     * Test Configurator Settings Update POST Endpoint
     */
    public function test_configurator_update_post_saves_settings(): void
    {
        $response = $this->post('/reliese/configurator', [
            'namespace' => 'App\\Models',
            'parent' => 'Illuminate\\Database\\Eloquent\\Model',
            'use_soft_deletes' => '1',
            'except' => 'migrations, failed_jobs',
        ]);

        $response->assertRedirect('/reliese/configurator');
        $response->assertSessionHas('success');
    }

    /**
     * Test ER Diagram Page Route
     */
    public function test_er_diagram_page_loads_successfully(): void
    {
        $response = $this->get('/reliese/er-diagram');

        $response->assertStatus(200);
        $response->assertSee('Database ER Diagram');
    }

    /**
     * Test ER Diagram Data JSON API Endpoint
     */
    public function test_er_diagram_data_json_api_returns_valid_structure(): void
    {
        $response = $this->getJson('/reliese/er-diagram-data');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'tables_count',
            'relationships_count',
            'data' => [
                'tables',
                'relationships',
                'mermaid'
            ]
        ]);
    }

    /**
     * Test Diff and Sandbox Page Route
     */
    public function test_diff_sandbox_page_loads_successfully(): void
    {
        $response = $this->get('/reliese/diff-sandbox?table=users');

        $response->assertStatus(200);
        $response->assertSee('Model Code Diff Inspector');
    }

    /**
     * Test Eloquent Sandbox Query Execution API Endpoint
     */
    public function test_sandbox_execute_api_runs_query(): void
    {
        $response = $this->postJson('/reliese/sandbox/execute', [
            'model' => 'User',
            'with' => '',
            'limit' => 5
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success'
        ]);
    }
}
