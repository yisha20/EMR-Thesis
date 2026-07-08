@extends('layouts.app')

@section('content')
@php
    $staff = [
        ['name' => 'MUHAMMAD M. PUTING, M.D.', 'role' => 'Chief Administrative Officer', 'initials' => 'MP'],
        ['name' => 'MIKKA ANGELA S. AYTONA', 'role' => 'Admin Assistant I', 'initials' => 'MA'],
        ['name' => 'OMADLE, ADONIS M.', 'role' => 'Admin Assistant I', 'initials' => 'AO'],
        ['name' => 'CEPE, CECILIA C.', 'role' => 'Nurse I', 'initials' => 'CC'],
        ['name' => 'CASTILLO, LOURDES MAE G.', 'role' => 'Nurse II', 'initials' => 'LC'],
        ['name' => 'GORECHO, IRENE B.', 'role' => 'Admin Aide VI', 'initials' => 'IG'],
        ['name' => 'MACKNO, PINAMILI R.', 'role' => 'Laboratory Technician III', 'initials' => 'PM'],
        ['name' => 'MACOTE, ASRIFAH S.', 'role' => 'Medical Technician II', 'initials' => 'AM'],
    ];
@endphp

<section class="section-heading">
    <p class="eyebrow">Clinic staff</p>
    <h1>MSU-IIT Office Clinic Team</h1>
    <span>Professional staff directory for medical, administrative, and laboratory support.</span>
</section>

<section class="staff-grid">
    @foreach($staff as $member)
        <article class="staff-card">
            <div class="staff-avatar">{{ $member['initials'] }}</div>
            <div>
                <h2>{{ $member['name'] }}</h2>
                <p>{{ $member['role'] }}</p>
            </div>
        </article>
    @endforeach
</section>
@stop
