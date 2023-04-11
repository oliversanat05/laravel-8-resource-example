<?php

namespace App\Http\Controllers\Api\V1\VMapCsv;

use App\Exports\VMapDataExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\csv\ImportVMapCsv;
use App\Imports\VMapDataImport;
use App\Models\Succession\VMap;
use App\Services\ExportCsv\ExportCsvService;
use App\Traits\ApiResponse;
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class VMapCsvController extends Controller
{
    use ApiResponse;

    public $exportCsv;

    public function __construct()
    {
        $this->exportCsv = new ExportCsvService();
    }

    /**
     * for creating the vmap csv
     *
     * @param Request $request
     * @return void
     */
    public function exportVmapCsv(Request $request)
    {

        try {
            $params = $request->query();

            $vmap = VMap::where('vMapId', $params['vmapId'])->first();

            $filename = $vmap->formTitle . '.csv';
            $data = Excel::download(new VMapDataExport($params), $filename);

            return $data;
        } catch (\Throwable$th) {
            throw $th;
        }
    }

    /**
     * for importing the vmap csv
     *
     * @param ImportVMapCsv $request
     * @return void
     */
    public function importVmapCsv(Request $request, $id)
    {
        try {

            DB::beginTransaction();

            $data = Excel::import(new VMapDataImport($id), $request->file('name'));

            DB::commit();

            if ($data) {
                return $this->successApiResponse(__('core.csvUploadSuccess'));
            } else {
                return $this->unprocessableApiResponse(__('core.csvUploadError'));
            }
        } catch (\Throwable$th) {

            // dd($th);
            return $th->getMessage();
            DB::rollback();
            return $this->errorApiResponse($th->getMessage());
        }
    }
}
