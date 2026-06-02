<?php

namespace App;

use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
	use SoftDeletes, SoftCascadeTrait;

	/**
	 * Fields that get deleted with.
	 * 
	 * @var Array
	 */
	protected $softCascade = [
		'healthExaminationRecord',
		'medicalRecords'
	];

	/**
	 * Fields that aren't mass assignable.
	 * 
	 * @var Array
	 */
	protected $guarded = [
		'created_at',
		'updated_at',
		'deleted_at',
	];

	/**
	 * Relationships always get called in Model.
	 * 
	 * @var Array
	 */
	protected $with = [
		'healthExaminationRecord',
	];

	/**
	 * This patient was added by this user.
	 * 
	 * @return BelongsTo
	 */
	public function addedBy()
	{
		return $this->belongsTo('App\User', 'added_by');
	}

	public function creator()
{
    return $this->belongsTo(User::class, 'added_by');
}


	/**
	 * This patient information was updated by this user.
	 * 
	 * @return BelongsTo
	 */
	public function updatedBy()
	{
		return $this->belongsTo('App\User', 'updated_by');
	}

	/**
	 * A patient has many medical records / consultations.
	 * 
	 * @return HasMany
	 */
	public function medicalRecords()
	{
		return $this->hasMany('App\MedicalRecord');
	}
	
	/**
	 * This patient has a health examination record.
	 * 
	 * @return HasOne
	 */
	public function healthExaminationRecord()
	{
		return $this->hasOne('App\HealthExaminationRecord');
	}

	public function getPastMedicalHistory()
	{
		return isset($this->healthExaminationRecord
		->past_medical_history['pastmedical_history']) ?
		$this->healthExaminationRecord
			->past_medical_history['pastmedical_history']
			: [];
	}
	
	/**
	 * Checks if patient has this past medical history.
	 * 
	 * @param String $data
	 * @return Boolean
	 */
	public function hasPastMedicalHistory($data)
	{
		return in_array($data, $this->getPastMedicalHistory());
	}

	/**
	 * Gets the menstrual pattern of a patient.
	 * 
	 * @return String
	 */
	public function getMenstrualPattern()
	{
		return $this->getPastMedicalHistoryAttr('menstrual_pattern');
	}

	/**
	 * Gets the last menstrual period of a patient.
	 * 
	 * @return String
	 */
	public function getLastMenstrualPeriod()
	{
		return $this->getPastMedicalHistoryAttr('last_menstrual_period');
	}

	/**
	 * Checks if patient has this past medical history.
	 * 
	 * @param String $data
	 * @return Boolean
	 */
	public function hasFamilyHistory($data)
	{
		return in_array($data, $this->getFamilyHistoryAttr('family_history'));
	}

	/**
	 * Gets the social history information of Patient.
	 * 
	 * @return AssocArray
	 */
	public function getFamilyHistory()
	{
		return $this->healthExaminationRecord->family_history;
	}
	
	/**
	 * Gets the social history information of Patient.
	 * 
	 * @return AssocArray
	 */
	public function getSocialHistory()
	{
		return $this->healthExaminationRecord->social_history;
	}

	/**
	 * Gets the physical examination of Patient.
	 * 
	 * @return AssocArray
	 */
	public function getPhysicalExamination()
	{
		return $this->healthExaminationRecord->phyiscal_examination;
	}

	/**
	 * Gets the vital signs of Patient.
	 * 
	 * @return AssocArray
	 */
	public function getVitalSigns()
	{
		return $this->healthExaminationRecord->vital_signs;
	}

	/**
	 * Gets the assessment of Patient.
	 * 
	 * @return AssocArray
	 */
	public function getAssessment()
	{
		return $this->healthExaminationRecord->assessment;
	}

	/**
	 * Gets the nursing interventions of Patient.
	 * 
	 * @return AssocArray
	 */
	public function getNursingInterventions()
	{
		return isset($this->healthExaminationRecord->nursing_interventions['nursing_interventions'])
			? $this->healthExaminationRecord->nursing_interventions['nursing_interventions'] : [];
	}

	/**
	 * Gets the PE attribute.
	 * 
	 * @param String
	 * @return String
	 */
	public function getPastMedicalHistoryAttr($key) 
	{
		return isset($this->healthExaminationRecord->past_medical_history[$key]) ?
			($this->healthExaminationRecord->past_medical_history[$key]) : '';
	}
	
	/**
	 * Gets the PE attribute.
	 * 
	 * @param String
	 * @return String
	 */
	public function getPEAttr($key) 
	{
		return isset($this->getPhysicalExamination()[$key]) ?
			($this->getPhysicalExamination()[$key]) : '';
	}
	
	/**
	 * Gets the Assessment attribute.
	 * 
	 * @param String
	 * @return String
	 */
	public function getAssessmentAttr($key) 
	{
		return isset($this->getAssessment()[$key]) ?
			($this->getAssessment()[$key]) : '';
	}

	/**
	 * Gets the Vital Sign attribute.
	 * 
	 * @param String
	 * @return String
	 */
	public function getVitalSignAttr($key) 
	{
		return isset($this->getVitalSigns()[$key]) ?
			($this->getVitalSigns()[$key]) : '';
	}

	/**
	 * Gets the Social History attribute.
	 * 
	 * @param String
	 * @return String
	 */
	public function getSocialHistoryAttr($key) 
	{
		return isset($this->getSocialHistory()[$key]) ?
			($this->getSocialHistory()[$key]) : '';
	}

	/**
	 * Gets the Social History attribute.
	 * 
	 * @param String
	 * @return String
	 */
	public function getFamilyHistoryAttr($key) 
	{
		return isset($this->getFamilyHistory()[$key]) ?
			($this->getFamilyHistory()[$key]) : [];
	}
}
