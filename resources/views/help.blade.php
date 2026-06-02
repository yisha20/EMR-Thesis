@extends('layouts.app')

@section('content')
  @if ($message = Session::get('success'))
    <div class="alert alert-success alert-block">
        <button type="button" class="close" data-dismiss="alert">×</button>	
        <strong>{{ $message }}</strong>
    </div>
  @endif

<div class="card text-center">
  <div class="card-header">
    <ul class="nav nav-tabs card-header-tabs">
      <li class="nav-item">
        <a class="nav-link active" href="/users">Help</a>
      </li>
    </ul>
  </div>
  <div class="card-body">
      <table class="table table-bordereds table-responsive-sm">
        <div class="input-group mb-4" style="margin:auto;max-width:300px">
          <input type="search"  placeholder="Search..." aria-describedby="button-addon5" class="form-control">
          <div class="input-group-append">
            <button id="button-addon5" type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
          </div>
        </div>
          <thead class="text-center thead-light">
            <tr>
              <th scope="col">Help</th>
              <th scope="col">Description</th>
            </tr>
          </thead>
          <tbody class="text-left">
              <tr>
                  <td>1. How to View Patient</td>
                  <td>Go to Manage Patient Module, Input Name of the Patient on the Search Bar to find patient faster,
                     Click View, and redirected to  patients medical record.</td>
              </tr>
              <tr>
                <td>2. How to Add Patient</td>
                <td>Go to Manage Patient Module, Click Add New Patient, 
                  Let the Patient fill up the form, then click Save.</td>
            </tr>
            <tr>
                <td>3. How to Edit Patient Form</td>
                <td>Go to Manage Patient Module, Input Name of the Patient on the Search Bar,
                   Click the "Edit" button,and the system will redirect you to patients form.
                  Fill up desired information you want to change or update. Click Save Button.</td>
            </tr>
            <tr>
                <td>4. How to Remove A Patient to the List</td>
                <td>Go to Manage Patient Module, Search for the patient you wish to remove,
                  Click archive button, The system will pop a message click "Yes" if you're sure to remove 
                  the patient.
                  </td>
            </tr>
            <tr>
                <td>5. How to Retrieve Patient Medical Record</td>
                <td>Go to Manage Patient Module, Click archive, input the name of the Patient on the searchbar, 
                  then click restore button.</td>
            </tr>
            <tr>
                <td>6. How to View Users</td>
                <td>Go to Manage Users Module, Search for the user name,
                  Click "View" button, the system will redirect you to the users info.</td>
            </tr>
            <tr>
                <td>7. How to Add New User (For admin Only)</td>
                <td>Go to Manage Users Module, Click "Add New Users" Navigation Bar,
                   Let the new user fill up the form, then click Register button.</td>
            </tr>
            <tr>
                <td>8. How to Edit User Form</td>
                <td>Go to Manage Users Module, Search for the user you wish to update,
                   Fill up the form, then click "save" button.</td>
            </tr>
            <tr>
                <td>9. How to Remove User</td>
                <td>Go to Manage Users Module, Search for the user you wish to remove,
                   press the Archive button, then click restore button.</td>
            </tr>
            <tr>
                <td>10. How to Restore User Data</td>
                <td>Go to Manage Users Module, Click "Archive" Nav bar, 
                  Search for the user you wish to restore data, then click restore button.</td>
            </tr>
            <tr>
              <td>11. How to View Services</td>
              <td>Go to Manage Services Module, the system will show lists of services, 
                click the "view" button, then the system will show the Service, Description, Added by &
                Date Added.</td>
          </tr>
          <tr>
            <td>12. How to Add New Service</td>
            <td>Go to Manage Service Module, Click "Add New Service" Nav bar, 
              Fill up the form, then click submit button</td>
        </tr>
        <tr>
          <td>13. How to Edit Service</td>
          <td>Go to Manage Users Module, Click "Edit" button of the service you wish to update, 
            edit the form, then click submit button</td>
      </tr>
      <tr>
        <td>14. How to Remove Service</td>
        <td>Go to Manage Services Module, Click "Archive" button of the service you wish to remove.</td>
          </tr>
          <tr>
            <td>15. How to Restore Service Data</td>
            <td>Go to Manage Services Module, Click "Archive" Nav bar, 
              Click restore button of the service you  wish to restore.</td>
        </tr>
        <tr>
          <td>16. How to View Medical Record of a Patient</td>
          <td>Go to Manage Patient Module, Search for Patient to view medical record,
            Click "View" button,the system will redirect you to patient data, under Profile Picture of the patient,
          Click "View Medical Record", the system will show the Patients medical record.</td>
      </tr>
      <tr>
        <td>17. How to Add New Medical Record</td>
        <td>On the "View Medical Record" of the Patient, Click Add new record,
          fill up the form, then click submit button.</td>
      </tr>
      <tr>
        <td>18. How to View Active Users</td>
        <td>From the Homepage, on the right side of the footer, click "Active User" button,
          then active users will pop out.</td>
      </tr>
      </tbody>
    </table>
  </div>
</div>
@stop