@extends('layouts.master')
@section('title', 'Kyc View')
@section('content')
<?php

use App\Models\User;
?>
<x-a-breadcrumb :columns="[
        [
            'url' => '/',
            'label' => 'Home',
        ],
        [
            'url' => 'kyc',
            'label' => 'Kyc',
        ],
        $model->name,
    ]" />

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="card-body">
                    <h5>{{ !empty($model->name) ? (strlen($model->name) > 100 ? substr($model->name, 0, 100) . '...' : $model->name) : 'N/A' }}
                        <span class="{{ $model->getStateBadgeOption() }}">{{ $model->getState() }}</span>
                    </h5>

                    <x-a-detail-view :model="$model" :type="'double'" :column="
                            [
                            'id',
                            'name',
                            'email',
                            'contact_number',
                            'national_id',
                                [
                                    'attribute' => 'type_id',
                                    'label' => 'National type',
                                    'value' => $model->gettype(),
                                    'visible' => true,
                                ],
                            [
                                'attribute' => 'updated_at',
                                'label' => 'Updated at',
                                'value' => (empty($model->updated_at)) ? 'N/A' : date('Y-m-d h:i:s A', strtotime($model->updated_at)),
                                'visible'=> true   
                            ],
                            [
                                'attribute' => 'created_at',
                                'label' => 'Created at',
                                'value' => (empty($model->created_at)) ? 'N/A' : date('Y-m-d h:i:s A', strtotime($model->created_at)),
                            ],
                            [
                                'attribute' => 'created_by_id',
                                'label' => 'Created By',
                                'value' => !empty($model->createdBy && $model->createdBy->name) ? $model->createdBy->name : 'N/A',
                            ],
                            ]
                            " />
                </div>
            </div>
        </div>
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-lg-4">

                        <div class="card-header">
                            <h3>Selfie Images</h3>
                        </div>
                        <div class="card-body">
                            <img src="{{ asset('uploads/'.$model->selfie_image) }}" alt="" srcset="" width="80px" height="100px">
                        </div>
                    </div>

                    <div class="col-lg-4">

                        <div class="card-header">
                            <h3>Front Images</h3>
                        </div>
                        <div class="card-body">
                            <img src="{{ asset('uploads/'.$model->front_image) }}" alt="" srcset="" width="80px" height="100px">
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card-header">
                            <h3>Back Images</h3>
                        </div>
                        <div class="card-body">
                            <img src="{{ asset('uploads/'.$model->back_image) }}" alt="" srcset="" width="80px" height="100px">
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card-header">
                            <h3>Short Video</h3>
                        </div>
                        <div class="card-body">
                            <video controls width="320" height="240">
                                <source src="{{ asset('uploads/'.$model->video) }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <h5 class="card-header">{{ __('Comments') }}</h5>
                <div class="card-body">
                    <div class="mt-2 ">
                        <form method="post" action="{{ route('comment.add') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="col-md-12 required">
                                <label class="pt-2 fw-bold" for="btncheck1">Comment</label>
                                <textarea id="comment-comment" class="form-control" name="comment" rows="6" aria-required="true" required></textarea>
                            </div>

                                <input type="hidden" class="form-control" value="{{$model->id}}" name="model_id">
                                <input type="hidden" class="form-control" value="{{get_class($model)}}" name="model_type">

                            <div class="col-12 text-right mt-4">
                                <button class="btn btn-outline-primary " type="submit">Add</button>
                            </div>
                        </form>

                        <div class="content-list content-image menu-action-right">
                            <ul class="list-wrapper">
                                <div id="w1" class="list-view comment-list">
                                    <div class="summary"></div>
                                    <div class="item" data-key="1">
                                        @if(!empty($model->comments))
                                        @foreach($model->comments as $comment)
                                        <li>
                                            <div class="items">
                                                <div class="menu-icon">
                                                    <img class="img-responsive" src="{{ asset($comment->createdBy->profile_image ? '/uploads/' . $comment->createdBy->profile_image : 'assets/img/avatars/1.png') }}" width="50" height="50" alt="{{$comment->createdBy->name}}">
                                                </div>
                                                <div class="menu-text">
                                                    <p>{{$comment->comment}} </p>
                                                    <ul class="nav" style="display: inline;"></ul>
                                                </div>
                                                <div class="menu-text">
                                                    <div class="menu-info">
                                                        {{$comment->createdBy->name}} - <span class="menu-date">{{(empty($comment->created_at)) ? 'N/A' : date('Y-m-d h:i:s A', strtotime($comment->created_at))}} </span>
                                                    </div>

                                                    <div class="menu-text" style="text-align: right">
                                                        <a class="badge badge-danger" href="/football-sports-betting-yii2-2069/comment/comment/delete?id=1&amp;title=csdfs" data-method="POST" data-confirm="Are you sure you want to delete this ?"><i class="fa fa-times"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        @endforeach

                                        @endif
                                    </div>
                            </ul>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(User::isAdmin())
        <div class="col-lg-12 mb-4 order-0">
            <x-a-user-action :model="$model" attribute="state_id" :states="$model->getStateOptions()" />
        </div>

        @endif
    </div>

</div>
@endsection