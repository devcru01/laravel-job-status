<?php

namespace Imtigger\LaravelJobStatus;

trait Trackable
{
    /** @var int */
    protected $statusId;
    protected $progressNow = 0;
    protected $progressMax = 0;
    protected $shouldTrack = true;

    /**
     * @var JobStatus
     */
    public $jobStatus;

    /**
     * @var int
     */
    public $temporaryLimit;

    public function setProgressMax($value)
    {
        $this->update(['progress_max' => $value]);
        $this->progressMax = $value;
    }

    public function setProgressNow($value, $every = 1)
    {
        if ($value % $every === 0 || $value === $this->progressMax) {
            $this->update(['progress_now' => $value]);
        }
        $this->progressNow = $value;
    }

    public function incrementProgress($offset = 1, $every = 1)
    {
        $value = $this->progressNow + $offset;
        $this->setProgressNow($value, $every);
    }

    public function setInput(array $value)
    {
        $this->update(['input' => $value]);
    }

    public function setOutput(array $value)
    {
        $this->update(['output' => $value]);
    }

    public function setTemporaryLimit($value)
    {
        $this->temporaryLimit = $value;
    }

    public function unsetTemporaryLimit()
    {
        $this->temporaryLimit = null;
    }

    public function update(array $data)
    {
        /** @var JobStatusUpdater */
        $updater = app(JobStatusUpdater::class);
        $jobStatus = $updater->update($this, $data);

        if ($jobStatus) {
            $this->jobStatus = $jobStatus;
        }
    }

    public function prepareStatus(array $data = [])
    {
        if (!$this->shouldTrack) {
            return;
        }

        /** @var JobStatus */
        $entityClass = app(config('job-status.model'));

        $data = array_merge(['type' => $this->getDisplayName()], $data);
        /** @var JobStatus */
        $status = $entityClass::query()->create($data);

        $this->statusId = $status->getKey();
        $this->jobStatus = $status;
    }

    public function getDisplayName()
    {
        return method_exists($this, 'displayName') ? $this->displayName() : static::class;
    }

    public function getJobStatusId()
    {
        return $this->statusId;
    }

    public function getJobStatus()
    {
        return $this->jobStatus;
    }
}
