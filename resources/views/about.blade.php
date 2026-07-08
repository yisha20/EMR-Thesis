@extends('layouts.app')

@section('content')
<section class="info-hero about-hero">
    <div>
        <p class="eyebrow">About the system</p>
        <h1>Electronic Medical Record for MSU-IIT Clinic</h1>
        <p>A digital workspace designed to help clinic personnel manage health profiles, patient histories, examinations, and service records with less paper-based friction.</p>
    </div>
    <img src="{{ asset('img/msuiit-clinic.png') }}" alt="MSU-IIT Clinic building">
</section>

<section class="info-grid">
    <article class="info-card">
        <span class="info-icon"><i class="fa fa-heartbeat"></i></span>
        <h2>Clinic Care</h2>
        <p>The Institute Clinic supports students and employees by providing medical and health services throughout their stay on campus, including urgent health concerns.</p>
    </article>
    <article class="info-card">
        <span class="info-icon"><i class="fa fa-folder-open-o"></i></span>
        <h2>Record Management</h2>
        <p>The EMR centralizes patient profiles, medical histories, physical examinations, nursing interventions, assessments, and recommendations.</p>
    </article>
    <article class="info-card">
        <span class="info-icon"><i class="fa fa-clock-o"></i></span>
        <h2>Faster Retrieval</h2>
        <p>Clinic staff can search, update, archive, restore, and review records more efficiently than traditional paper-based workflows.</p>
    </article>
</section>

<section class="feature-panel">
    <div>
        <p class="eyebrow">System capabilities</p>
        <h2>Designed for daily clinic operations</h2>
    </div>
    <div class="feature-list">
        <span><i class="fa fa-check"></i> Add and update patient profiles</span>
        <span><i class="fa fa-check"></i> Record medical history and examinations</span>
        <span><i class="fa fa-check"></i> Search patient records quickly</span>
        <span><i class="fa fa-check"></i> Archive and restore medical records</span>
        <span><i class="fa fa-check"></i> Manage authorized users</span>
        <span><i class="fa fa-check"></i> Maintain clinic service information</span>
    </div>
</section>
@stop
