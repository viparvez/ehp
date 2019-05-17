<?php 

namespace App;

use Zizaco\Entrust\EntrustPermission;

class Permission extends EntrustPermission
{
	protected $fillable = ['name', 'display_name', 'description'];

	public function CreatedBy() {
        return $this->belongsTo('App\User', 'createdbyuser_id');
    }

    public function UpdatedBy() {
        return $this->belongsTo('App\User', 'updatedbyuser_id');
    }
}