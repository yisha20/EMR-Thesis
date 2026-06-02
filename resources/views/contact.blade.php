@extends('layouts.app')

@section('content')
<style>
  .jumbotron{
    background: linear-gradient(
    rgba(0, 0, 0, 0.5),
    rgba(0, 0, 0, 0.5)
  ), url("img/2.jpg")  ;
    background-position: center;
    color: white;
  }
  </style>
  
<section class="jumbotron text-center">
  <div class="container">
      <h1 class="jumbotron-heading"><b>Get In Touch</b></h1>
      <p class="lead mb-0">Want to get in touch? We'd love to here from you. Here's how you can reach us...</p>
  </div>
</section>
<br>
<div class="container">
  <div class="row ">
      <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6 text-center">
        <img class="img-responsive" style="height: 50%" src="/img/msuiit.png">
        <br><br>
        <p>Seal of Excellence</p>
      </div>
      <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
        <h5>MSU-Iligan Institute of Technology</h5>
        <p><i class="fa fa-globe" aria-hidden="true"> www.msuiit.edu.ph</p></i>
        <p><i class="fa fa-phone" aria-hidden="true"> +63 (063) 221-4056</p></i>
        <br>
        <h5>Registrar</h5>
        <p><i class="fa fa-envelope" aria-hidden="true"> registrar@g.msuiit.edu.ph</p></i>
        <p><i class="fa fa-phone" aria-hidden="true"> +63 (063) 223 3794</p></i>
      </div>
      <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
        <h5>Institute Clinic</h5>
        <p><i class="fa fa-phone" aria-hidden="true"> 4444 (local)</p></i>
        <br>
        <h5>Admissions</h5>
        <p><i class="fa fa-envelope" aria-hidden="true"> admissions@g.msuiit.edu.ph</p></i>
        <p><i class="fa fa-phone" aria-hidden="true"> +63 (063) 223 8641</p></i>
      </div>
      <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
        <h5>Address: </h5>
        <h6>
        Andres Bonifacio Ave, Iligan City, 
        9200 Lanao del Norte
        <p>Philippines</p></h6>
      </div>
  </div>

  
  <div class="card" style="background-color: gray">
          <div class="card-header text-white"><i class="fa fa-envelope"></i> Developers Contact Information.
          </div>

    <div class="card-body">
            <div class="row">
              <div class="col">
                <h4 >Anne Hayathi S. Albiso</h4>   
                <hr style="width:100%;text-align:left;margin-left:0">                                         
                      <p class="title"><em>EMR System Developer</em></p>
                      <p style="text-align:left;">Course: BS Information Technology - Multimedia</p>
                      <p style="text-align:left;"><i class="fa fa-envelope" aria-hidden="true"> annehayathi1@gmail.com</p></i>
                      <p style="text-align:left;"><i class="fa fa-phone" aria-hidden="true"> 0915-565-0790 / 0927-549-0750</p></i>
                      <p style="text-align:left;"><i class="fa fa-facebook-square" aria-hidden="true"> https://www.facebook.com/anneyourlife.anneong</small></p></i> 
              </div>
              <div class="col">
                <h4>Isnaina U. Abdulazis</h4>
                <hr style="width:100%;text-align:left;margin-left:0">
                      <p class="title"><em>EMR System Developer</em></p>
                      <p style="text-align:left;">Course: BS Information Technology - Multimedia</p>
                      <p style="text-align:left;"><i class="fa fa-envelope" aria-hidden="true"> isnainaabdulazis@gmail.com</p></i>
                      <p style="text-align:left;"><i class="fa fa-phone" aria-hidden="true"> 0910-041-1645 / 0927-499-3905</p></i>
                      <p style="text-align:left;"><i class="fa fa-facebook-square" aria-hidden="true"> https://www.facebook.com/ISNIE05</small></p></i>
              </div>
            </div>
    </div>
  </div>
  <br><br>

@stop