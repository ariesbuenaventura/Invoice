<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use Response;

class InvoiceController extends Controller
{
    public function __construct(Invoice $invoice) {
        $this->middleware('auth');

        $this->invoice = $invoice;
    }

    public function index()
    {
        return view('invoices.index');
    }

    public function Invoice($id, $action) {
        $invoice = null;
        
        if($id != '') {
            $invoice = $this->invoice->GetInvoice(utf8_encode(base64_decode($id)));
        }

        return view('invoices.invoice')->with('id', $id)
                                       ->with('action', $action)
                                       ->with('invoice', $invoice);
    }

    public function Save(Request $request) {
        $data = json_decode($request->data);

        if($data->id != '') {
            $data->id = utf8_encode(base64_decode($data->id));

            return $this->invoice->SaveInvoice($data);
        }
    }

    public function Delete(Request $request) {
        $id = $request->id;

        if($id != '') {
            $id = utf8_encode(base64_decode($id));

            return $this->invoice->DeleteInvoice($id);
        }
    }

    public function Search($id = 0) {
        return Response::json($this->invoice->Search($id));
    }
}
