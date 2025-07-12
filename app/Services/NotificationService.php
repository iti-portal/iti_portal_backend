<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\JobApplication;

class NotificationService
{
    /**
     * Create a notification for a user
     */
    public function createNotification(int $userId, string $type, string $title, string $message, array $data = []): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'is_read' => false,
        ]);
    }

    /**
     * Notify company when a new application is submitted
     */
    public function notifyCompanyOfNewApplication(JobApplication $application): void
    {
        $applicant = $application->user;
        $job = $application->job;
        $company = $job->company;

        $this->createNotification(
            $company->id,
            'new_application',
            'New Job Application Received',
            "A new application has been submitted for your job posting '{$job->title}' by {$applicant->full_name}.",
            [
                'application_id' => $application->id,
                'job_id' => $job->id,
                'applicant_id' => $applicant->id,
                'applicant_name' => $applicant->full_name,
                'job_title' => $job->title,
            ]
        );
    }

    /**
     * Notify applicant when application status changes
     */
    public function notifyApplicantOfStatusChange(JobApplication $application, string $oldStatus): void
    {
        $applicant = $application->user;
        $job = $application->job;
        $company = $job->company;

        $statusMessages = [
            'reviewed' => 'Your application is being reviewed',
            'interviewed' => 'You have been selected for an interview',
            'hired' => 'Congratulations! You have been hired',
            'rejected' => 'Your application was not selected this time',
        ];

        $title = match($application->status) {
            'reviewed' => 'Application Under Review',
            'interviewed' => 'Interview Invitation',
            'hired' => 'Job Offer - Congratulations!',
            'rejected' => 'Application Update',
            default => 'Application Status Update',
        };

        $message = $statusMessages[$application->status] ?? 'Your application status has been updated';
        $message .= " for the position '{$job->title}' at {$company->full_name}.";

        $this->createNotification(
            $applicant->id,
            'application_status_change',
            $title,
            $message,
            [
                'application_id' => $application->id,
                'job_id' => $job->id,
                'company_id' => $company->id,
                'old_status' => $oldStatus,
                'new_status' => $application->status,
                'job_title' => $job->title,
                'company_name' => $company->full_name,
            ]
        );
    }

    /**
     * Notify company when an application is withdrawn
     */
    public function notifyCompanyOfWithdrawal(JobApplication $application): void
    {
        $applicant = $application->user;
        $job = $application->job;
        $company = $job->company;

        $this->createNotification(
            $company->id,
            'application_withdrawn',
            'Application Withdrawn',
            "{$applicant->full_name} has withdrawn their application for the position '{$job->title}'.",
            [
                'application_id' => $application->id,
                'job_id' => $job->id,
                'applicant_id' => $applicant->id,
                'applicant_name' => $applicant->full_name,
                'job_title' => $job->title,
            ]
        );
    }
}