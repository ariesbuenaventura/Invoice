<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'INVOICE';

    public function SaveInvoice($data) {
        try {              
            // is ID equal to zero?  
            if((int)$data->id == 0) {
                // yes, add new invoice.

                // check if invoice number already exists.
                $query = Self::select()->where('number', '=', $data->number)
                                       ->first();

                // is invoice number alredy exists?    
                if(empty($query)) {
                    // no, insert invoice
                    $id = Self::insertGetId([ 
                                                  'number'=>$data->number, 
                                                    'date'=>date_format(new \DateTime($data->date), "Y-m-d H:i:s"),
                                                    'name'=>$data->name, 
                                              'created_at'=>date("Y-m-d H:i:s"),
                                              'updated_at'=>date("Y-m-d H:i:s")
                                            ]);

                    if(count($data->products)>0) {
                        foreach($data->products as $product) {
                            DB::Table('products')
                              ->insert([
                                            'invoiceid'=>$id,
                                            'product'=>$product->product,
                                            'quantity'=>$product->quantity,
                                                'price'=>$product->price,
                                        'created_at'=>date("Y-m-d H:i:s"),
                                        'updated_at'=>date("Y-m-d H:i:s")
                                      ]);
                        }
                    }

                    return [ 'id'=>$id, 'success'=>true, 'message'=>'Successfully Added.' ];
                } else {
                    // yes, return error
                    return [ 'id'=>0, 'success'=>false, 'message'=>'Invoice number already exist!' ];  
                } 
            } else {
                // check if invoice ID is already exists.
                $query = Self::select()->where('id', '=', $data->id)
                                       ->first();

                // is invoice ID alredy exists?    
                if(!empty($query)) {
                   // yes, update the invoice
                    Self::where('id', '=', (int)$data->id)
                        ->update([ 
                                        'date'=>date_format(new \DateTime($data->date), "Y-m-d H:i:s"),
                                        'name'=>$data->name, 
                                      'number'=>$data->number, 
                                    'updated_at'=>date("Y-m-d H:i:s")
                                 ]);
                         
                    if(count($data->products)>0) {
                        $filters = [];

                        foreach($data->products as $product) {
                            $filters[] = $product->id;
                        }

                        if(count($filters)>0) {
                            DB::Table('products')          
                              ->where('invoiceid', '=', (int)$data->id)
                              ->whereNotIn('id', $filters)
                              ->delete();
                        }

                        foreach($data->products as $product) {
                            // is product exists.
                            $query = DB::Table('products')
                                       ->where('id', '=', (int)$product->id)
                                       ->where('invoiceid', '=', (int)$data->id)
                                       ->first();

                            if(empty($query)) {
                                DB::Table('products')
                                    ->insert([
                                                      'invoiceid'=>$data->id,
                                                        'product'=>$product->product,
                                                       'quantity'=>$product->quantity,
                                                          'price'=>$product->price,
                                                     'created_at'=>date("Y-m-d H:i:s"),
                                                     'updated_at'=>date("Y-m-d H:i:s")
                                            ]);
                            } else {
                                DB::Table('products')
                                  ->where('id', '=', (int)$product->id)
                                  ->update([
                                                   'product'=>$product->product,
                                                  'quantity'=>$product->quantity,
                                                     'price'=>$product->price,
                                                'updated_at'=>date("Y-m-d H:i:s")
                                          ]);   
                            } 
                        }
                    }
                    return [ 'id'=>$data->id, 'success'=>true, 'message'=>'Successfully Updated.' ];
                } else {
                    // no, return error
                    return [ 'id'=>$data->id, 'success'=>false, 'message'=>'Invoice number doest not exists!' ];  
                }
            }
        } catch (\Exception $ex) {
            return [ 'id'=>0, 'success'=>false, 'message'=>$ex->getMessage() ];  
        }
    }

    public function DeleteInvoice($id) {
        try {   
            Self::where('id', '=', (int)$id)
                ->delete();

            DB::Table('products')          
            ->where('invoiceid', '=', (int)$id)
            ->delete();

            return [ 'id'=>$id, 'success'=>true, 'message'=>'Deleted' ]; 
        } catch (\Exception $ex) {
            return [ 'id'=>$id, 'success'=>false, 'message'=>$ex->getMessage() ];  
        }
    }

    public function GetInvoice($id) {
        try {   
            $details = Self::select(['number', 'name', 'date'])
                        ->where('id', '=', $id)
                        ->first();

            if(!empty($details)) {
                $products = DB::Table('products')
                            ->where('invoiceid', '=', $id)
                            ->select(['id', 'product', 'quantity', 'price'])
                            ->orderBy('invoiceid')
                            ->get();

                return [ 'id'=>$id, 'success'=>true, 'message'=>'', 'details'=>$details, 'products'=>$products ];
            } else {
                return [ 'id'=>$id, 'success'=>true, 'message'=>'No Records Found.', 'details'=>null, 'products'=>null ]; 
            }
        } catch (\Exception $ex) {
            return [ 'id'=>$id, 'success'=>false, 'message'=>$ex->getMessage(), 'details'=>null, 'products'=>null ];  
        }
    }

    public function search($id = 0) {
        try {  
            $query = Self::select();
                         
            if($id>0) {
                $query = $query->where('id', '=', $id)
                               ->first();
            } else {
                $query = $query->get();
            }

            return ['success'=>true, 'message'=>'', 'list'=>$query ];
        } catch (\Exception $ex) {
            return [ 'success'=>false, 'message'=>$ex->getMessage(), 'list'=>null ];  
        }
    }

    public function test() {
        return 'xxxtest';
    }
}
