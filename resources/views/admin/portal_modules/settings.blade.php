@extends('admin.template')
@section('content')
<div class="main-content">
    @if(Session::has('success_msg'))
        <div class="alert alert-success" style="margin:10px 15px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <p class="text-center" style="margin-bottom:0;">{{ Session::get('success_msg') }}</p>
        </div>
    @endif

    <div class="container-fluid">
        <div class="portal-page-title">
            <div>
                <h1>Portal Integrations</h1>
                <p>Configure SMTP, API integration values, and AI feature toggles used by the admin portal.</p>
            </div>
        </div>

        <form method="post" action="{{URL::to('admin/portal-settings')}}">
            {!! csrf_field() !!}
            <div class="row portal-grid-gap">
                <div class="col-lg-6">
                    <div class="portal-card portal-section">
                        <div class="portal-section-header">
                            <h3>SMTP Integration</h3>
                        </div>
                        <div class="form-group">
                            <label>SMTP Host</label>
                            <input type="text" name="smtp_host" value="{{$settings['smtp_host'] ?? ''}}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>SMTP Port</label>
                            <input type="text" name="smtp_port" value="{{$settings['smtp_port'] ?? ''}}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>SMTP Username</label>
                            <input type="text" name="smtp_username" value="{{$settings['smtp_username'] ?? ''}}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>SMTP Password</label>
                            <input type="password" name="smtp_password" value="{{$settings['smtp_password'] ?? ''}}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Encryption</label>
                            <select name="smtp_encryption" class="form-control">
                                @foreach(['', 'tls', 'ssl'] as $option)
                                    <option value="{{$option}}" @if(($settings['smtp_encryption'] ?? '') === $option) selected @endif>{{$option ?: 'None'}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="portal-card portal-section">
                        <div class="portal-section-header">
                            <h3>API Integration</h3>
                        </div>
                        <div class="form-group">
                            <label>API Base URL</label>
                            <input type="text" name="api_base_url" value="{{$settings['api_base_url'] ?? ''}}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>API Key</label>
                            <input type="text" name="api_key" value="{{$settings['api_key'] ?? ''}}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>AI Auto-Fill</label>
                            <select name="ai_auto_fill_enabled" class="form-control">
                                <option value="1" @if(($settings['ai_auto_fill_enabled'] ?? '1') == '1') selected @endif>Enabled</option>
                                <option value="0" @if(($settings['ai_auto_fill_enabled'] ?? '1') == '0') selected @endif>Disabled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Integration Settings</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
