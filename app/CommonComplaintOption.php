<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class CommonComplaintOption extends Model { protected $guarded = []; protected $casts = ['is_active'=>'boolean','allows_multiple'=>'boolean','requires_details'=>'boolean']; }
