@extends('layouts.app')

@section('content')
   <div id="invoice" ng-app="InvoiceApp" ng-controller="InvoiceController" ng-init="init('{{ $id }}')" ng-cloak>
      <div class="invoice-container w-75">
         <div class="invoice-products card">
            <div class="card-header" style="color:white;background-color:black;">
               <div class="text-center"><b>INVOICE</b></div>
            </div>
            <div class="card-body">
               <div class="invoice-header">
                  <div class="row">
                     <div class="col">
                        <label><b><span class="required">*</span> Customer Name:</b>&nbsp;<span id="name" class="error"><i>(Customer Name is required)</i></label>
                        <input class="form-control" type="text" maxlength="255" ng-model="invoice.name" required {{ ($action=='v') || ($action=='d') ? 'readonly' : '' }}/>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col">
                        <label><b><span class="required">*</span> Invoice #:</b>&nbsp;<span id="number" class="error"><i>(Invoice # is required)</i></label>
                        <input class="form-control" type="text" maxlength="255" ng-model="invoice.number" required {{ ($action=='v') || ($action=='d') ? 'readonly' : '' }}>
                     </div>
                     <div class="col">
                        <label><b><span class="required">*</span> Invoice Date:</b>&nbsp;<span id="date" class="error"><i>(Invoice date is required)</i></label>
                        <input id="invoice-date" class="form-control" type="date" maxlength="20" value="{{ isset($invoice['details']) ? $invoice['details']->date : ''  }}"  required {{ ($action=='v') || ($action=='d') ? 'readonly' : '' }} />
                     </div>
                  </div>
               </div>
               <hr />

               @php($total  = 0.00)
               @php($amount = 0.00)
               <table id="products" class="w-100">
                  <thead>
                     <tr style="color:white;background-color:black;">
                        <th>Product</th>
                        <th class="text-right" style="width:20%">Quantity</th>
                        <th class="text-right" style="width:20%">Price</th>
                        <th class="text-right" style="width:20%">Amount</th>
                     </tr>
                  </thead>
                  <tbody>
                     @if(isset($invoice['products']))
                        @foreach($invoice['products'] as $data) 
                           @php($amount = $data->quantity * $data->price)
                           @php($total  = $total + $amount)

                           <tr data-id="{{ $data->id }}" class="item">
                              <td>
                                 <input class="form-control" data-type="product" type="text" maxlength="255" value="{{ $data->product }}" {{ ($action=='v') || ($action=='d') ? 'readonly' : '' }} />    
                              </td>
                              <td>
                                 <input class="form-control text-right" data-type="quantity" type="text" maxlength="10" value="{{ $data->quantity }}" {{ ($action=='v') || ($action=='d') ? 'readonly' : '' }} />    
                              </td>
                              <td>
                                 <input class="form-control text-right" data-type="price" type="text" maxlength="20" value="{{ $data->price }}" {{ ($action=='v') || ($action=='d') ? 'readonly' : '' }} />    
                              </td>
                              <td>
                                 <input class="form-control text-right" data-type="amount" type="text" value="{{ number_format($amount, 2) }}" readonly /> 
                              </td>
                           </tr>
                        @endforeach
                     @endif

                     @php($totalitem = isset($invoice['products']) ? 10-count($invoice['products']) : 10)

                     @for($i=0; $i<$totalitem; $i++)
                        <tr data-id="0" class="item">
                              <td>
                                 <input class="form-control" data-type="product" type="text" maxlength="255" value="" {{ ($action=='v') || ($action=='d') ? 'readonly' : '' }} />    
                              </td>
                              <td>
                                 <input class="form-control text-right" data-type="quantity" type="text" maxlength="10" value="0" {{ ($action=='v') || ($action=='d') ? 'readonly' : '' }} />    
                              </td>
                              <td>
                                 <input class="form-control text-right" data-type="price" type="text" maxlength="20" value="0.00" {{ ($action=='v') || ($action=='d') ? 'readonly' : '' }} />    
                              </td>
                              <td>
                                 <input class="form-control text-right" data-type="amount" type="text" value="0.00" readonly /> 
                              </td>
                        </tr>
                     @endfor
                  </tbody>
               </table>
               <hr />
               <div class="text-right">
                  <b>Total Invoice Amount: </b><span id="total" class="d-inline-block" style="width:100px;">{{ number_format($total, 2) }}</span>
               </div>
            </div>
         </div>
         <br />
         <div class="text-center">
            @if(($action=='c') || ($action=='e'))
               <button id="save" class="btn btn-sm btn-danger action-button ml-1 mr-1" ng-click="save()">Save</button>
            @elseif($action=='d')
               <button id="save" class="btn btn-sm btn-danger action-button ml-1 mr-1" ng-click="delete()">Delete</button>
            @endif

            <button id="cancel" class="btn btn-sm btn-secondary action-button" ng-click="cancel()">
               {{ ($action=='v') ? 'Back' : 'Cancel' }}
            </button>
         </div>
      </div>
   </div>
@endsection

@section('styles')
   <style>
      .required {
         color:red;
      }

      .error {
         color:red;
         font-size: small;
         display: none;
      }

      .action-button {
         width:100px;
      }

      .invoice-container {
         margin: 0 auto;
      }
   </style>
@endsection

@section('scripts')
   <script type="text/javascript" src="/js/accounting.min.js"></script>
   <script type="text/javascript">
      var app = angular.module('InvoiceApp', []);

      app.controller('InvoiceController', function ($scope, $http) {
         let $invoice = $("#invoice");

         $scope.invoice = {
                                    id: '',
                                number: "{{ isset($invoice['details']) ? $invoice['details']->number : '' }}",
                                  date: "{{ isset($invoice['details']) ? $invoice['details']->date : ''  }}",
                                  name: "{{ isset($invoice['details']) ? $invoice['details']->name : '' }}",
                              products: []
                           };

         $scope.init = function (id) {
            $scope.invoice.id = id;
         };

         $scope.save = function() {
            let isError = false;

            if($scope.invoice.name.trim() == "") {
               isError = true;
               $invoice.find("#name").show();
            } else {
               $invoice.find("#name").hide();  
            }

            if($scope.invoice.number.trim() == "") {
               isError = true;
               $invoice.find("#number").show();
            } else {
               $invoice.find("#number").hide();
            }

            if( $("#invoice-date").val()== "") {
               isError = true;
               $invoice.find("#date").show();
            } else {
               $invoice.find("#date").hide();
            }

            if(!isError) {
               $("#save").attr("disabled", true);

               $scope.invoice.products = [];

               $scope.invoice.date = $("#invoice-date").val();
               $invoice.find("#products .item").each(function(i, o) {
                  if($(o).find("input[data-type='product']").val().trim() != "") {
                     $scope.invoice.products.push({ 
                                                               'id': $(this).data("id"),
                                                          'product': $(o).find("input[data-type='product']").val(),
                                                         'quantity': accounting.unformat($(o).find("input[data-type='quantity']").val()),
                                                            'price': accounting.unformat($(o).find("input[data-type='price']").val()),         
                                                });
                  }
               });

               $http({
                              method: 'POST',
                           dataType: 'json',
                                 url: "/invoices/invoice/save",
                           headers: {
                                          'Content-Type': 'application/json'
                                    },
                              data: { 'data': JSON.stringify($scope.invoice) },
                        }).then(function(response) {
                              $("#save").removeAttr("disabled");

                              if(response.data.success) {
                                 alert(response.data.message);
                                 window.location.href = "/invoices";
                              } else {
                                 alert(response.data.message);
                              }
                        });
            } else {
               $("html, body").animate({scrollTop:  0  }, 250);
            }
         }

         $scope.delete = function() {
            if(confirm("Are you sure?")) {
               $http({
                              method: 'POST',
                           dataType: 'json',
                                 url: "/invoices/invoice/delete",
                           headers: {
                                          'Content-Type': 'application/json'
                                    },
                              data: { 'id': $scope.invoice.id },
                        }).then(function(response) {
                              $("#save").removeAttr("disabled");
                              if(response.data.success) {
                                 alert(response.data.message);
                                 window.location.href = "/invoices";
                              } else {
                                 alert(response.data.message);
                              }
                        });
            } 
         }

         $scope.cancel = () => {
            window.location.href = "/invoices";
         }

         $invoice.find("input[data-type='quantity'], input[data-type='price']")
                 .on("propertychange keyup paste input", function () {
            let item = $(this).parents(':eq(1)');

            let qty = accounting.unformat(item.find("input[data-type='quantity']").val());
            let prc = accounting.unformat(item.find("input[data-type='price']").val());
            let amt = accounting.formatMoney(qty*prc, "", 2, ",", ".");
            let sum = 0.00;

            item.find(item.find("input[data-type='amount']").val(amt));

            $invoice.find("#products input[data-type='amount']" ).each(function( index ) {
               sum = sum + accounting.unformat($(this).val());
            });

            $invoice.find("#total").text(accounting.formatMoney(sum, "", 2, ",", "."));
         });
      });
    </script>
@endsection