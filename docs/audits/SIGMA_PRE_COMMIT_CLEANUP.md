# SIGMA Pre-Commit Cleanup Audit

Audit date: 2026-10-15 (Asia/Bangkok)

## Scope and safety

This is a review of the current working tree before commit. No pull, fetch, merge, rebase, stage, commit, push, migration, application test, model inference, or file deletion was performed. All local data and feature changes remain present.

The current `git status --short --branch` reports `main...origin/main [behind 8]`, no staged changes, 26 modified tracked files (24 pre-existing feature edits plus two ignore files), and untracked feature/source/data files, including `frontend/apk_sigma/pubspec.lock`, which is now visible after removing its ignore rule. This agrees with the status supplied for this audit. Do not synchronize the branch until reviewing the eight incoming commits and coordinating around the local work.

## A. Recommend for the feature commit

These form connected runtime features. Review their diffs as a single feature set and stage only after reviewing the eight incoming commits.

### YOLO runtime integration

- Modified: `ai_service/ai_service_sigma/app/main.py` adds the detection router while retaining the fire-risk router.
- Modified: `ai_service/ai_service_sigma/requirements.txt` adds Ultralytics.
- New implementation: `ai_service/ai_service_sigma/routers/yolo_detection.py`, `ai_service/ai_service_sigma/services/yolo_detector.py`.
- Backend integration: modified `backend/web_sigma/app/Http/Controllers/Api/Citizen/ReportController.php` calls the configured AI endpoint, persists verification and annotated image metadata, and serves original/annotated photos; modified `backend/web_sigma/app/Models/AiReportVerification.php`, `backend/web_sigma/routes/api/citizen.php`, and `backend/web_sigma/database/migrations/2026_10_11_143746_add_annotated_image_path_to_ai_report_verifications_table.php` support that flow.
- Commit the code and dependency declaration together. Runtime initialization requires `models/firedetect-11s.pt` at the path in `services/yolo_detector.py`; deployment must supply this exact model by a documented artifact/download mechanism or include the licensed model in the deployment package. Its origin, license, class names, and deployment recovery are unresolved, so do not decide the model's Git storage policy without that information.

### Early Warning feature

- New backend: `backend/web_sigma/app/Http/Controllers/Api/Citizen/EarlyWarningController.php`, `backend/web_sigma/app/Http/Controllers/Web/Government/EarlyWarningController.php`, `backend/web_sigma/app/Models/EarlyWarning.php`, `backend/web_sigma/app/Models/EarlyWarningHistory.php`, `backend/web_sigma/app/Notifications/EarlyWarningChanged.php`, `backend/web_sigma/app/Services/EarlyWarningService.php`.
- Required migrations: `backend/web_sigma/database/migrations/2026_10_13_000000_create_early_warnings_table.php`, `backend/web_sigma/database/migrations/2026_10_13_000100_create_early_warning_histories_table.php`, and `backend/web_sigma/database/migrations/2026_10_14_000000_add_region_and_warning_idempotency.php`. They create the tables and then add region/idempotency fields; preserve all three in timestamp order.
- UI and routes: new `backend/web_sigma/public/css/government/early-warnings.css`, `backend/web_sigma/resources/views/government/early-warnings/**`; modified `backend/web_sigma/resources/views/layouts/government.blade.php`, `backend/web_sigma/routes/web.php`, and `backend/web_sigma/routes/api/citizen.php`.
- Citizen API integrations: modified `backend/web_sigma/app/Http/Controllers/Api/Citizen/NotificationController.php`, `NotificationStreamController.php`, `ProfileController.php`, `ReportController.php`, `backend/web_sigma/app/Http/Requests/Citizen/Profile/UpdateProfileRequest.php`, `backend/web_sigma/app/Http/Resources/Citizen/ProfileResource.php`, `ReportResource.php`, and `backend/web_sigma/app/Models/User.php`.
- Flutter: new `frontend/apk_sigma/lib/features/citizen/early_warnings/**`; modified `frontend/apk_sigma/lib/features/citizen/dashboard/pages/citizen_dashboard_page.dart`, `frontend/apk_sigma/lib/features/citizen/data/datasources/citizen_remote_data_source.dart`, `frontend/apk_sigma/lib/features/citizen/data/models/citizen_models.dart`, `frontend/apk_sigma/lib/features/citizen/notifications/citizen_notification_store.dart`, `frontend/apk_sigma/lib/features/citizen/notifications/pages/notification_page.dart`, `frontend/apk_sigma/lib/features/citizen/profile/pages/profile_page.dart`, and `frontend/apk_sigma/lib/features/citizen/reports/pages/report_detail_page.dart`.
- `backend/web_sigma/database/seeders/EarlyWarningDevelopmentCitizensSeeder.php` is an optional development fixture seeder. It is not called from `DatabaseSeeder`; it rejects non-local/non-testing environments and requires explicit `SIGMA_EW_SEED_DEMO_CITIZENS` and `SIGMA_EW_DEMO_PASSWORD` values. It is reasonable to commit as development tooling, but must only be invoked manually with those safeguards.

### Dependency lock

`frontend/apk_sigma/pubspec.lock` exists locally (28,380 bytes) but was ignored by the root `.gitignore` and is not tracked. This is an application, so committing the lockfile improves reproducibility. I removed that ignore entry; review the generated lockfile before adding it to a future commit.

## B. Tests and development scripts

| Path | Finding | Recommendation |
|---|---|---|
| `ai_service/ai_service_sigma/tests/test_yolo.py` | Standalone smoke script; loads `yolo11n.pt`, reads `tests/images/test.jpg`, and prints detections. It has no assertions and may rely on Ultralytics downloading a checkpoint. | Keep the script and fixture if the team uses this smoke check, but do not describe it as automated regression coverage. Make checkpoint acquisition/version explicit before relying on it in CI. |
| `ai_service/ai_service_sigma/test_fire_smoke_model.py` | Manual inference script for `models/firedetect-11s.pt` and `test2.jpeg`; writes results under `runs/detect/test_fire_smoke`. | Keep as a local/manual smoke tool; do not treat as a deterministic automated test. `runs/` is ignored. |
| `ai_service/ai_service_sigma/check_dataset.py` | Diagnostic reads selected label text files from `downloads/fire_smoke/smoke/data`; no runtime import found. | Keep if this dataset inspection is still part of development; its download path is currently not a documented clean setup. |
| `ai_service/ai_service_sigma/validate_yolo_dataset.py` | Manual data integrity validator for image/label pairs, bounding boxes, and expected class IDs 0/1 under `dataset/yolo_fire_smoke`; prints a result but does not currently return a failing process exit code. | Recommend commit as a development utility together with a portable class-mapping manifest. Actual dataset validation was not run; do not rely on it as a CI gate until its exit behavior is fixed. |
| `ai_service/ai_service_sigma/prepare_yolo_dataset.py` | Copies from `downloads/fire_smoke/smoke/data`, rewrites labels and YAML, but calls `shutil.rmtree(target)` before copying and embeds a machine-specific absolute path. | REVIEW REQUIRED before committing as shared tooling. It can delete the generated target and the input path is not currently documented/recovered. Do not run it during this cleanup. |

## C. Local experiments, samples, models, and data

No local file was deleted. Do not stage the following data as part of the application commit unless separately approved and licensed.

| Path | Working-tree evidence / references | Recommendation |
|---|---|---|
| `ai_service/ai_service_sigma/dataset/yolo_fire/**` | About 65,207 files / 2,133,651,676 bytes. Includes `data.yaml`, plus large image and label trees. | Keep local for training/evaluation; image and label trees are specifically ignored. No reproducible acquisition/backup instructions found. |
| `ai_service/ai_service_sigma/dataset/yolo_fire_smoke/**` | About 43,055 files / 3,117,684,669 bytes. Includes `data.yaml`, plus large image and label trees. Used by `validate_yolo_dataset.py`. | Keep local for training/evaluation; image and label trees are specifically ignored. The manifest has an absolute local path; fix to a portable path before treating the manifest as ready for team use. |
| `ai_service/ai_service_sigma/downloads/fire_smoke/**` | Referenced by `check_dataset.py` and `prepare_yolo_dataset.py`. The split contents were not enumerated successfully in this pass; ignore rules cover only `train/`, `val/`, and `test/` beneath this exact dataset path. | Keep local; no confirmed source URL/license or recovery steps, so the data is not safely reproducible from a clean checkout. |
| `ai_service/ai_service_sigma/models/firedetect-11s.pt` | 19,222,483 bytes. Explicitly loaded at runtime by `services/yolo_detector.py`. | Required deployment artifact. Keep the local copy; do not ignore or delete until delivery is documented. Do not add to Git until source/license and repository artifact policy are confirmed. |
| `ai_service/ai_service_sigma/yolo11n.pt` | 5,613,764 bytes. Referenced by `tests/test_yolo.py`, not by the runtime service. | Experiment/test checkpoint only. Local copy retained and exact file ignored; it is not recommended for the application commit. |
| `ai_service/ai_service_sigma/bus.jpg` (137,419 bytes), `test1.jpeg` (117,372), `test_fire.png` (340,151), `test_images/**` (2 files / 164,437 bytes) | No source references found in the inspected Python code. | Local samples only; retained and exact paths ignored to prevent accidental inclusion. |
| `ai_service/ai_service_sigma/test2.jpeg` (47,065 bytes) | Directly referenced by `test_fire_smoke_model.py`. | Retain as that manual script's input. Do not include without confirming redistribution rights and team need. |
| `ai_service/ai_service_sigma/tests/images/test.jpg` (part of `tests/`, 51,628 bytes across two files) | Directly referenced by `tests/test_yolo.py`. | Keep with the smoke script if it is used; confirm image provenance/license. The `tests/` directory itself is not ignored. |
| `ai_service/ai_service_sigma/runs/**` | Three current generated files / 383,626 bytes; inference script writes there. | Local output, reproducible by inference; retained and ignored at the exact `runs/` path. |

The two training trees alone total about 5.25 GB (4,251,336,345 bytes). These figures are local working-tree sizes; the directories are not staged and do not appear in committed history based on the previous path audit. The smaller untracked source and feature paths remain visible in Git status.

## D. Documentation

`docs/` currently contains:

- `docs/audits/SIGMA_REPOSITORY_CLEANUP.md`: prior broad cleanup audit. It overlaps with this more focused report. Keep the file locally, but omit it from this feature commit unless the team wants both audit documents; do not delete it as part of cleanup.
- `docs/audits/SIGMA_PRE_COMMIT_CLEANUP.md`: this task's requested pre-commit report. Recommend including it if the team keeps audit records in the repository.

No setup guide or API documentation currently exists under `docs/` according to the file inventory. No other documentation was deleted.

## E. Ignore rules reviewed/changed

- Root `.gitignore`: narrowed SQLite rules to the Laravel database directory, removed the global `*.sql` rule because no backup path was established, narrowed geospatial extension rules to the AI raw data directory, and scoped `_tmp_*.log` to repository root. Removed the `pubspec.lock` ignore entry so the Flutter application lockfile can be reviewed for commit.
- `ai_service/ai_service_sigma/.gitignore`: keeps the earlier narrow ignores for YOLO dataset image/label trees, downloaded split folders, and generated `runs/`; additionally ignores only `bus.jpg`, `test1.jpeg`, `test_fire.png`, `test_images/`, and `yolo11n.pt` as local samples/test model.
- Neither ignore file removes tracked files from Git. No `git rm --cached` was run. Runtime model `models/firedetect-11s.pt`, test2.jpeg, test source, and `tests/` are not ignored.
- Check `git check-ignore -v` results before any later broad `git add`; `pubspec.lock` should now no longer match an ignore rule.

## F. Secret and personal-data review

- No `.env`, credential file, or database file appeared in the supplied/current status list.
- A regex scan of PHP, Python, Dart, Blade, JSON, and YAML source for likely literal assignments to API keys, passwords, secrets, or tokens returned no matching file paths. This is a heuristic scan, not a guarantee; review any environment/config changes and run the team's secret scanner before staging.
- YOLO datasets and sample images may contain third-party or personal imagery. Their provenance and redistribution permissions were not established, which is another reason to keep them out of the application commit.

## G. Validation performed

- Ran `git status --short --branch`, `git diff --stat`, `git diff --cached --stat`, `git ls-files` for the Flutter lockfile, `git check-ignore -v` for candidate local files and the lockfile, and `git diff --check`.
- The current branch status reports eight commits behind and no staged changes. `git diff --cached --stat` was empty. `git diff --check` had no whitespace errors; Git emitted pre-existing LF-to-CRLF warnings for three modified Dart files.
- Inspected the ignore files, documentation inventory, YOLO import/model references, seeder call graph, relevant migration names, scripts, and feature references.
- No application tests, Flutter analysis, Laravel route checks, inference, training, or migration was run. Feature correctness is not verified by this audit.
- No model checkpoint was loaded. Its class names (`fire`/`smoke`) remain unverified.

## H. Files changed by cleanup

- `.gitignore`: narrowed broad data ignores and made the Flutter lockfile eligible for tracking.
- `ai_service/ai_service_sigma/.gitignore`: added exact local sample/test-model ignores.
- `docs/audits/SIGMA_PRE_COMMIT_CLEANUP.md`: created this report.
- The previous `docs/audits/SIGMA_REPOSITORY_CLEANUP.md` was left untouched. No feature implementation, model, dataset, image, or user change was removed or overwritten. Nothing was staged, committed, pushed, or synchronized.

## Safe commit preparation

1. Review the eight incoming commits without pulling, and decide how to integrate them while preserving this working tree.
2. Resolve the runtime model's source/license and deployment delivery method before deciding how the required checkpoint reaches production.
3. Confirm dataset source/license/backup and make YAML paths portable; review the destructive behavior of `prepare_yolo_dataset.py` before sharing it.
4. Review secret scan and image provenance, then selectively stage the feature source/migrations/resources and chosen tests/docs. Exclude the multi-gigabyte datasets and unapproved local sample/model assets.
5. Inspect the staged diff and run the feature-specific checks before commit. This audit did not stage, commit, or push anything.
