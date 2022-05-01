@extends('layouts.app')

@section('content')
    <div id="invoices" ng-app="InvoiceApp" ng-controller="InvoiceController" ng-init="init()" ng-cloak>
        <div class="invoices-container w-75">
            <a href="/invoices/invoice/@{{ btoa(0) }}/c" class="btn btn-small btn-dark" style="width:100px;">Create</a>
            <hr />
            <table id="invoices-list" class="invoices w-100" border="1">
                <thead>
                    <tr style="color:white;background-color:black;">
                        <th class="p-1">Invoice Number</th>
                        <th class="p-1 text-center">Invoice Date</th>
                        <th class="p-1">Customer Name</th>
                        <th style="width:260px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="invoice in invoices">
                        <td class="p-1">@{{ invoice.number }}</td>
                        <td class="p-1 text-center">@{{ invoice.date }}</td>
                        <td class="p-1">@{{ invoice.name }}</td>
                        <td class="text-center">
                            <a href="/invoices/invoice/@{{ btoa(invoice.id) }}/v" class="btn btn-small btn-dark action-button">View</a>
                            <a href="/invoices/invoice/@{{ btoa(invoice.id) }}/e" class="btn btn-small btn-dark action-button">Edit</a>
                            <a href="/invoices/invoice/@{{ btoa(invoice.id) }}/d" class="btn btn-small btn-dark action-button">Delete</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .action-button {
            width:70px;font-size:small;
        }

        .invoices-container {
            margin: 0 auto;
        }

        .hidden {
            display: none;
        }
    </style>
@endsection

@section('scripts')
    <script type="text/javascript">
        var app = angular.module('InvoiceApp', []);

        app.controller('InvoiceController', function ($scope, $http) {
            $scope.invoices = null;

            $scope.init = function () {
                $scope.search();
            };

            $scope.search = function () {
                $http.get(`/invoices/search`)
                     .then((results) => {
                         console.log(results.data.list);
                        $scope.invoices = results.data.list;
                     });
            }

            $scope.btoa = function(id) {
                // just a simple encryption for id parameter.
                return btoa(id);
            }
        });
    </script>
@endsection