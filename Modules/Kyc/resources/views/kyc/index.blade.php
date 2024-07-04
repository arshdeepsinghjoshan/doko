@extends('layouts.master')
@section('title', 'Accounts')

@section('content')



<x-a-breadcrumb :columns="[
        [
            'url' => '/',
            'label' => 'Home',
        ],
        [
            'url' => 'kyc',
            'label' => 'Kyc',
        ],
    ]" />

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">

                <h5 class="card-header">{{ __('Index') }}</h5>

                <div class="card-body">
                    <x-a-update-menu-items :model="$model" :action="'index'" />
                    <div class="table-responsive">
                        <x-a-grid-view :id="'kyc_table'" :model="$model" :url="'kyc/get-list'" :columns="[
                                'id',
                                'name',
                                'email',
                                'contact_number',
                                'type_id',
                                'created_at',
                                'status',
                                'created_by',
                                'action',
                            ]" />
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection