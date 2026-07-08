@extends('layouts.app')

@section('content')
<div class="border border-info consultation-edit-page">
    <div class="card-header border-info">
        <ul class="nav nav-tabs card-header-tabs mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('medical-records.show', $patient->id) }}">Medical Record</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('medical-records.show', $patient->id) }}">View / Edit Consultation</a>
            </li>
            <li class="nav nav-item-right ml-auto">
                <a href="{{ route('patients.show', $patient->id) }}"><button type="button" class="close">&times;</button></a>
            </li>
        </ul>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-block">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>{{ $message }}</strong>
        </div>
    @endif

    @if ($errors->any())
        <div class="container mt-5">
            <div class="alert alert-danger alert-block">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="card-body">
        <form action="{{ route('medical-records.update', $medicalRecord->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div id="printThis" class="consultation-edit-canvas">
                <div class="consultation-edit-header">
                    <h1>View / Edit Consultation</h1>
                    <div class="consultation-meta-row">
                        <div>
                            <span>OPD / ID NUMBER</span>
                            <strong>{{ $patient->id_number }}</strong>
                        </div>
                        <div>
                            <span>DATE / TIME</span>
                            <strong>{{ $medicalRecord->getDateTimeConsultation() }}</strong>
                            <input type="hidden" name="date_of_consultation" value="{{ optional($medicalRecord->date_of_consultation)->format('Y-m-d') }}">
                            <input type="hidden" name="time_of_consultation" value="{{ $medicalRecord->time_of_consultation }}">
                        </div>
                        <div>
                            <span>ATTACHED FILE</span>
                            @if ($medicalRecord->file)
                                <a href="{{ $medicalRecord->file }}" target="_blank" class="consultation-file-link">File <i class="fa fa-paperclip"></i></a>
                            @else
                                <strong>No file</strong>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="form-group consultation-vitals-row">
                    <label class="consultation-vitals-label"><b>Vital Signs</b></label>
                    <div class="col-sm-2">
                        <div class="form-inline">
                            <label for="temp" class="col-form-label">T</label>
                            <input type="number" step="0.1" class="form-control-sm col-sm-5" name="vital_signs[temperature]" id="temp" value="{{ $medicalRecord->vital_signs['temperature'] ?? '' }}">
                            <label class="col-form-label"><i><small>Â°C</small></i></label>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-inline">
                            <label for="pulse_rate" class="col-form-label">PR</label>
                            <input type="number" class="form-control-sm col-sm-5" name="vital_signs[pulse_rate]" id="pulse_rate" value="{{ $medicalRecord->vital_signs['pulse_rate'] ?? '' }}">
                            <label class="col-form-label"><i><small>bpm</small></i></label>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-inline">
                            <label for="res_rate" class="col-form-label">RR</label>
                            <input type="number" class="form-control-sm col-sm-5" name="vital_signs[respiratory_rate]" id="res_rate" value="{{ $medicalRecord->vital_signs['respiratory_rate'] ?? '' }}">
                            <label class="col-form-label"><i><small>bpm</small></i></label>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-inline">
                            <label for="b_p" class="col-form-label">BP</label>
                            <input type="text" class="form-control-sm col-sm-5" name="vital_signs[blood_pressure]" id="b_p" value="{{ $medicalRecord->vital_signs['blood_pressure'] ?? '' }}">
                            <label class="col-form-label"><i><small>mmHg</small></i></label>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-inline">
                            <label for="weight" class="col-form-label">WT</label>
                            <input type="number" step="0.1" class="form-control-sm col-sm-5" name="vital_signs[weight]" id="weight" value="{{ $medicalRecord->vital_signs['weight'] ?? '' }}">
                            <label class="col-form-label"><i><small>kg</small></i></label>
                        </div>
                    </div>
                </div>

                <div class="consultation-edit-grid">
                    <section>
                        <div class="form-group">
                            <label for="chief_complaint"><b>Chief Complaint</b></label>
                            <textarea class="form-control" id="chief_complaint" rows="2" name="chief_complaint">{{ $medicalRecord->chief_complaint }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="history_p_i"><b>History of Present Illness</b></label>
                            <textarea class="form-control" id="history_p_i" rows="3" name="history_of_present_illness">{{ $medicalRecord->history_of_present_illness }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="medication_taken"><b>Medication Taken</b></label>
                            <textarea class="form-control" id="medication_taken" rows="2" name="medication_taken">{{ $medicalRecord->medication_taken }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="findings"><b>Findings</b></label>
                            <textarea class="form-control" id="findings" rows="2" name="findings">{{ $medicalRecord->findings }}</textarea>
                        </div>
                    </section>

                    <section>
                        <div class="form-group">
                            <label for="services"><b>Service</b></label>
                            <select class="form-control" id="services" name="performed_service" required>
                                <option class="hidden" selected value="{{ $medicalRecord->performed_service }}">{{ $medicalRecord->performed_service }}</option>
                                @foreach(\App\Service::get() as $service)
                                    <option value="{{ $service->name }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="recommendation"><b>Recommendations</b></label>
                            <textarea class="form-control" id="recommendation" rows="3" name="recommendation">{{ $medicalRecord->recommendation }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="diagnosis"><b>Diagnosis</b></label>
                            <textarea class="form-control" id="diagnosis" rows="3" name="diagnosis">{{ $medicalRecord->diagnosis }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="file"><b>Attach File</b></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="file" name="file">
                                <label class="custom-file-label" for="file">Choose file</label>
                            </div>
                        </div>
                    </section>
                </div>

                <input type="hidden" name="attending_physician" value="{{ auth()->user()->fullName() }}">
                <div class="consultation-audit-note">
                    <span><b>Attending physician:</b> {{ $medicalRecord->attending_physician }}</span>
                    <span><b>To be updated by:</b> {{ auth()->user()->fullName() }}</span>
                </div>
            </div>

            <div class="modal-footer consultation-action-footer">
                <button id="btnPrint" type="button" class="btn btn-outline-info">Print</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
                <a href="{{ route('medical-records.show', $patient->id) }}"><button type="button" class="btn btn-secondary">Close</button></a>
            </div>
        </form>
    </div>
</div>

<style>
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
</style>

<script>
    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip();
    });

    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });

    document.getElementById("btnPrint").onclick = function () {
        printElement(document.getElementById("printThis"));
    }

    function printElement(elem) {
        var domClone = elem.cloneNode(true);
        var $printSection = document.getElementById("printSection");

        if (!$printSection) {
            $printSection = document.createElement("div");
            $printSection.id = "printSection";
            document.body.appendChild($printSection);
        }

        $printSection.innerHTML = "";
        $printSection.appendChild(domClone);
        window.print();
    }
</script>
@stop
