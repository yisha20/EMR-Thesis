@extends('layouts.app')

@section('content')
<section class="section-heading contact-heading">
    <p class="eyebrow">Contact information</p>
    <h1>Get in touch with MSU-IIT</h1>
    <span>Structured contact points for the institute, clinic, registrar, admissions, and system developers.</span>
</section>

<section class="contact-layout">
    <aside class="contact-brand-card">
        <img src="{{ asset('img/msuiit.png') }}" alt="MSU-IIT seal">
        <h2>MSU-Iligan Institute of Technology</h2>
        <p>Andres Bonifacio Avenue, Iligan City, 9200 Lanao del Norte, Philippines</p>
    </aside>

    <div class="contact-grid">
        <article class="contact-card">
            <span><i class="fa fa-globe"></i></span>
            <h3>Website</h3>
            <p>www.msuiit.edu.ph</p>
        </article>
        <article class="contact-card">
            <span><i class="fa fa-phone"></i></span>
            <h3>Institute</h3>
            <p>+63 (063) 221-4056</p>
        </article>
        <article class="contact-card">
            <span><i class="fa fa-stethoscope"></i></span>
            <h3>Institute Clinic</h3>
            <p>4444 local</p>
        </article>
        <article class="contact-card">
            <span><i class="fa fa-envelope-o"></i></span>
            <h3>Registrar</h3>
            <p>registrar@g.msuiit.edu.ph</p>
        </article>
        <article class="contact-card">
            <span><i class="fa fa-envelope-o"></i></span>
            <h3>Admissions</h3>
            <p>admissions@g.msuiit.edu.ph</p>
        </article>
        <article class="contact-card">
            <span><i class="fa fa-map-marker"></i></span>
            <h3>Location</h3>
            <p>Iligan City, Lanao del Norte</p>
        </article>
    </div>
</section>

<section class="developer-panel">
    <p class="eyebrow">Developer contacts</p>
    <div class="developer-grid">
        <article>
            <h3>Anne Hayathi S. Albiso</h3>
            <p>BS Information Technology - Multimedia</p>
            <span><i class="fa fa-envelope-o"></i> annehayathi1@gmail.com</span>
            <span><i class="fa fa-phone"></i> 0915-565-0790 / 0927-549-0750</span>
        </article>
        <article>
            <h3>Isnaina U. Abdulazis</h3>
            <p>BS Information Technology - Multimedia</p>
            <span><i class="fa fa-envelope-o"></i> isnainaabdulazis@gmail.com</span>
            <span><i class="fa fa-phone"></i> 0910-041-1645 / 0927-499-3905</span>
        </article>
    </div>
</section>
@stop
