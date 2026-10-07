@extends('admin.template')
@section('content')
<div class="main-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-block text-center" style="padding: 70px 20px;">
                <div style="width:64px;height:64px;margin:0 auto 20px;border-radius:50%;background:#fff3cd;color:#946200;display:flex;align-items:center;justify-content:center;font-size:30px;">
                    <i class="ti-lock"></i>
                </div>
                <h3 style="margin-bottom:10px;">Permission Denied</h3>
                <p style="max-width:520px;margin:0 auto 24px;color:#64748b;">
                    Your role does not have permission to open this WMS module.
                </p>
                <a href="{{URL::to('admin/dashboard')}}" class="btn btn-primary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>
@endsection
