@extends('layouts.loginlayout')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">

                <div class="card-body">
                <form action="" method="post" id="frmLogin">
                @csrf

                        <div class="jumbotron text-center">
                            <h1>Electronic Medical Record</h1>
                             </div>

	<div class="error-message"><?php if(isset($message)) { echo $message; } ?></div>	
	<div class="form-group row">
    <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-M             ail Address') }}</label>
        <div class="col-md-6">
		<div><input name="member_name" type="email" value="<?php if(isset($_COOKIE["member_login"])) { echo $_COOKIE["member_login"]; } ?>" class="input-field">
        </div>
	</div>
    
	<div class="form-group row">
    <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

        <div class="col-md-6">
		<div><input name="member_password" type="password" value="" class="input-field"> 
        </div>
	</div>
	<div class="form-group row">
		<div><input type="checkbox" name="remember" id="remember" <?php if(isset($_COOKIE["member_login"])) { ?> checked <?php } ?> />
		<label for="remember-me">Remember me</label>
	</div>
	<div class="form-group row">
		<div><input type="submit" name="login" value="Login" class="form-submit-button"></span></div>
	</div>       
</form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

