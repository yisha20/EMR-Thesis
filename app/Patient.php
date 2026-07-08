<?php

namespace App;

use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
	use SoftDeletes, SoftCascadeTrait;

	protected $dates = [
		'date_registered',
		'last_reviewed_at',
		'deleted_at',
	];

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

	public function getAvatarAttribute($value)
	{
		if (! $value) {
			return null;
		}

		$path = parse_url($value, PHP_URL_PATH);

		if ($path && strpos($path, '/storage/') === 0) {
			return $path;
		}

		return $value;
	}

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

	public function archivedBy()
	{
		return $this->belongsTo('App\User', 'archived_by');
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

	public function studentComplaints()
	{
		return $this->hasMany(StudentComplaint::class);
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
		$history = $this->normalizeStructuredValue(optional($this->healthExaminationRecord)->past_medical_history);

		return $this->normalizeListValue(
			$history['pastmedical_history'] ?? []
		);
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
		return $this->normalizeStructuredValue(optional($this->healthExaminationRecord)->family_history);
	}
	
	/**
	 * Gets the social history information of Patient.
	 * 
	 * @return AssocArray
	 */
	public function getSocialHistory()
	{
		return $this->normalizeStructuredValue(optional($this->healthExaminationRecord)->social_history);
	}

	/**
	 * Gets the physical examination of Patient.
	 * 
	 * @return AssocArray
	 */
	public function getPhysicalExamination()
	{
		return $this->normalizeStructuredValue(optional($this->healthExaminationRecord)->phyiscal_examination);
	}

	/**
	 * Gets the vital signs of Patient.
	 * 
	 * @return AssocArray
	 */
	public function getVitalSigns()
	{
		return $this->normalizeStructuredValue(optional($this->healthExaminationRecord)->vital_signs);
	}

	/**
	 * Gets the assessment of Patient.
	 * 
	 * @return AssocArray
	 */
	public function getAssessment()
	{
		return $this->normalizeStructuredValue(optional($this->healthExaminationRecord)->assessment);
	}

	/**
	 * Gets the nursing interventions of Patient.
	 * 
	 * @return AssocArray
	 */
	public function getNursingInterventions()
	{
		$interventions = $this->normalizeStructuredValue(
			optional($this->healthExaminationRecord)->nursing_interventions
		);

		return $this->normalizeListValue(
			$interventions['nursing_interventions'] ?? []
		);
	}

	/**
	 * Gets the PE attribute.
	 * 
	 * @param String
	 * @return String
	 */
	public function getPastMedicalHistoryAttr($key) 
	{
		$history = $this->normalizeStructuredValue(optional($this->healthExaminationRecord)->past_medical_history);

		return $history[$key] ?? '';
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
		$value = $this->getSocialHistory()[$key] ?? null;

		if (in_array($key, ['medications', 'allergies'], true)) {
			return $this->normalizeListValue($value);
		}

		return $value ?? '';
	}

	/**
	 * Gets the Social History attribute.
	 * 
	 * @param String
	 * @return String
	 */
	public function getFamilyHistoryAttr($key) 
	{
		return $this->normalizeListValue($this->getFamilyHistory()[$key] ?? []);
	}

	private function normalizeStructuredValue($value)
	{
		if ($value instanceof \Illuminate\Support\Collection) {
			return $value->toArray();
		}

		if (is_array($value)) {
			return $value;
		}

		if (is_string($value)) {
			$trimmed = trim($value);
			if ($trimmed === '') {
				return [];
			}

			$decoded = json_decode($trimmed, true);
			if (json_last_error() === JSON_ERROR_NONE) {
				return is_array($decoded) ? $decoded : [$decoded];
			}

			return strpos($trimmed, ',') !== false
				? array_values(array_filter(array_map('trim', explode(',', $trimmed)), 'strlen'))
				: [$trimmed];
		}

		return $value === null ? [] : [$value];
	}

	private function normalizeListValue($value)
	{
		$normalized = $this->normalizeStructuredValue($value);

		return array_values($normalized);
	}
}
