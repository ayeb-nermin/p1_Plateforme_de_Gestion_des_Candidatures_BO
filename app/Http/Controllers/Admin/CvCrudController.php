<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CvRequest;
use App\Models\Cv;
use App\Traits\CrudPermissions;
use App\Traits\Field;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;


/**
 * Class ReclamationCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CvCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
    //     store as traitStore;
    // }
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
    //     update as traitUpdate;
    // }
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use Field;
    use CrudPermissions;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Cv::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/cvs');
        CRUD::setEntityNameStrings('Cv', 'Cvs');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        //get the CVs of the users that has applied only
        // $this->crud->query = $this->crud->model->where();

        $this->crud->addColumn([
            'name' => 'title',
            'label' => 'Title',
        ]);

        $this->crud->addColumn([
            'name' => 'description',
            'label' => 'Description',
        ]);
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {

        $this->crud->addField([
            'name' => 'title',
            'label' => 'Title',
            'type' => 'text',
        ]);

        $this->crud->addField([
            'name' => 'description',
            'label' => 'Description',
            'type' => 'tinymce',
            'options' => [
                'allow_html' => true, // This allows HTML content
            ],
        ]);
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
    protected function store()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $redirect = $this->traitStore();
        $id = $this->crud->entry->id;
        return $redirect;
    }
    protected function update()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $redirect = $this->traitUpdate();
        $id = $this->crud->entry->id;
        return $redirect;
    }
    protected function setupShowOperation()
    {
        $this->crud->set('show.setFromDb', false);
        $this->crud->addColumn([
            'name' => 'title',
            'label' => 'Title',
        ]);

        CRUD::column('description')
            ->type('custom_html')
            ->value(CRUD::getCurrentEntry()->description ?? '')
            ->escaped(false)
            ->label('Description');


        $this->crud->addColumn([
            'name' => 'created_at',
            'label' => 'Date de création',
        ]);

        // Add the download button
        $this->crud->addButtonFromView('line', 'download', 'btn_download', 'beginning');
    }

    public function downloadPdf(Request $request, Cv $cv)
    {
        // Create a new instance of Dompdf
        $dompdf = new Dompdf();

        // Load HTML content (view) into Dompdf
        $html = view('pdf.cv', compact('cv'))->render();
        $dompdf->loadHtml($html);

        // Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Generate a unique filename for the PDF
        $filename = 'cv_' . $cv->id . '.pdf';

        // Save the PDF to the public directory
        Storage::disk('public')->put('pdf/' . $filename, $dompdf->output());

        // Get the public URL for the saved PDF
        $pdfUrl = asset('pdf/' . $filename);

        // Return a redirect response to the PDF URL for download
        return response()->redirectTo($pdfUrl);
    }
}
