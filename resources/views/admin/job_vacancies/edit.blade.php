@extends('layouts.admin')

@section('content')
    <div class="container py-5">
        <h2 class="mb-4">Edit Job Vacancy</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.job_vacancies.update', $jobVacancy->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="title" class="form-label">Job Title</label>
                <input type="text" class="form-control" name="title" id="title"
                    value="{{ old('title', $jobVacancy->title) }}" required>
            </div>

            <div class="mb-3">
                <label for="job_type" class="form-label">Job Type</label>
                <select class="form-control" name="job_type" id="job_type" required>
                    <option value="">Select Job Type</option>
                    <option value="teaching" {{ old('job_type', $jobVacancy->job_type) == 'teaching' ? 'selected' : '' }}>
                        Teaching</option>
                    <option value="non_teaching"
                        {{ old('job_type', $jobVacancy->job_type) == 'non_teaching' ? 'selected' : '' }}>Non-Teaching
                    </option>
                </select>
            </div>

            <div class="mb-3">
                <label for="employment_status" class="form-label">Employment Status</label>
                <select class="form-control" name="employment_status" id="employment_status" required>
                    <option value="">Select Employment Status</option>
                    <option value="permanent"
                        {{ old('employment_status', $jobVacancy->employment_status) == 'permanent' ? 'selected' : '' }}>
                        Permanent
                    </option>
                    <option value="part_time"
                        {{ old('employment_status', $jobVacancy->employment_status) == 'part_time' ? 'selected' : '' }}>
                        Part-Time
                    </option>
                </select>
            </div>

            <div class="mb-3">
                <label for="campus" class="form-label">Campus</label>
                <input type="text" class="form-control" name="campus" id="campus"
                    value="{{ old('campus', $jobVacancy->campus) }}" required>
            </div>

            <div class="mb-3">
                <label for="department" class="form-label">Department</label>
                <input type="text" class="form-control" name="department" id="department"
                    value="{{ old('department', $jobVacancy->department) }}" required>
            </div>

            <div class="mb-3">
                <label>Course</label>
                <input type="text" class="form-control" name="course" id="course"
                    value="{{ old('course', $jobVacancy->course) }}" required>
                <small class="text-muted">E.g., BS Computer Science, BS Midwifery</small>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Job Description</label>
                <textarea class="form-control" name="description" id="description" rows="4">{{ old('description', $jobVacancy->description) }}</textarea>
            </div>

            <div class="mb-3">
                <label for="qualifications" class="form-label">Qualifications (comma-separated)</label>
                <textarea class="form-control" name="qualifications" id="qualifications" rows="3">{{ old('qualifications', $jobVacancy->qualifications) }}</textarea>
                <small class="text-muted">E.g., Bachelor's Degree, PHP, Laravel</small>
            </div>

            <button type="submit" class="btn btn-primary">Update Job Vacancy</button>
            <a href="{{ route('admin.job_vacancies.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
