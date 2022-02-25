<?php

namespace Imtigger\LaravelJobStatus;

interface TrackableJob
{
    public function getJobStatusId();

    public function getJobStatus();
}
