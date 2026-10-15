# SIGMA Repository Cleanup Audit

Audit date: 2026-10-15 (Asia/Bangkok)

## A. Summary

- Repository root: `D:\laragon\www\SIGMA`; components present: Laravel backend, Flutter app, and FastAPI AI service.
- Before cleanup, `main` was six commits behind `origin/main`. There were 24 modified tracked files and numerous untracked feature files, YOLO scripts, datasets, images, and model weights. No staged changes were reported.
- No user files or datasets were deleted, moved, reset, staged, committed, or pushed. The only cleanup changes are narrow ignore rules for local YOLO training data and generated YOLO run output, plus this report.
- Large repository risks remain: tracked historical risk datasets/models, a 3.5 MB tracked `_ai.txt` snapshot of unknown purpose, and Git reporting an unexpected 1.33 GiB garbage temporary pack. Do not run garbage collection or delete Git internals as part of this cleanup.

## B. File Classification

Paths below are relative to the repository root. Sizes are working-tree sizes when measured; `not measured` means a per-file size was not obtained during this audit.

| Path or precise path group | Git status | Function / references | Deletion impact and reproducibility | Decision |
|---|---|---|---|---|
| `ai_service/ai_service_sigma/models/fire_risk_model.pkl` | Tracked | Loaded as `MODEL_PATH` by `config.py`; named in fire risk router and training code. | Runtime prediction model; rebuild/recovery not established. | KEEP |
| `ai_service/ai_service_sigma/dataset/processed/*.csv` (six files) and `dataset/raw/**` | Tracked | Data pipeline/model inputs; includes fire history, hotspot, weather, land cover, risk feature CSVs and raster inputs. | Needed for reproducible training/data processing; deletion could break workflows. | KEEP |
| `ai_service/ai_service_sigma/models/firedetect-11s.pt` | Untracked | Loaded by `services/yolo_detector.py`, imported by the current local `app/main.py` changes. | Required by the current YOLO runtime path. Origin, license, size, and recovery procedure not verified. | KEEP; REVIEW REQUIRED for distribution |
| `ai_service/ai_service_sigma/yolo11n.pt` | Untracked | Used by `tests/test_yolo.py`; no FastAPI runtime reference found. | Test dependency; provenance and license not verified. | KEEP; REVIEW REQUIRED |
| `ai_service/ai_service_sigma/dataset/yolo_fire/**`, `dataset/yolo_fire_smoke/**`, `downloads/fire_smoke/**` | Untracked | Training inputs. Preparation/validation scripts refer to these paths. YAML manifests describe fire/smoke classes, but dataset integrity and active training need are not established. | Preserve local copies. Recovery source and backup are not verified. | KEEP BUT IGNORE image/label and train/val/test data; YAML manifests remain visible to Git |
| `ai_service/ai_service_sigma/runs/**` | Untracked | Inference outputs; `test_fire_smoke_model.py` writes under this directory. | Regenerable local results; existing contents retained. | KEEP BUT IGNORE |
| `ai_service/ai_service_sigma/test1.jpeg`, `test2.jpeg`, `test_fire.png`, `bus.jpg`, `test_images/**`, `tests/**` | Untracked | Local sample/test inputs; test scripts refer to some of these. | Could be needed for manual validation; no deletion performed. | REVIEW REQUIRED |
| `check_dataset.py`, `prepare_yolo_dataset.py`, `validate_yolo_dataset.py`, `test_fire_smoke_model.py`, `routers/yolo_detection.py`, `services/yolo_detector.py` | Untracked | Source and local tooling, including current runtime integration. | Not disposable artifacts; some are needed for current feature work. | KEEP |
| Root `_ai.txt` (3,509,772 bytes), `_ai_dirs.txt` (30), `_ai_ds.txt` (768), `_ai_files.txt` (952), `_ai_http.txt` (87), `_ai_openapi.json` (293), `_ai_proc.txt` (255), `_ai_region.json` (22), `_ai_risk.json` (22), `_ai_root.json` (48), `_ports.txt` (77) | Tracked, unchanged | Metadata/snapshots; `_ai_files.txt` lists a model path and `_ai.txt` contains a local path to the risk model. Repository source/config search found no consumer references. Their producer and intended operational use were not identified. | Some could be regenerated, but the generation procedure and purpose are unknown; deleting tracked metadata could remove useful team context. | REVIEW REQUIRED; retained |
| Root `_tmp_fac_full.log` (13,716 bytes), `_tmp_lc_full.log` (635 bytes) | Ignored local files | Temporary-looking logs, matched by root `.gitignore` rule `_tmp_*.log`; no consumer references found. | Likely reproducible, but provenance/content significance not verified. Retained. | KEEP BUT IGNORE; REVIEW REQUIRED before deletion |
| `backend/web_sigma/vendor/`, `frontend/apk_sigma/.dart_tool/`, Flutter `build/`, Python `__pycache__/` and `*.pyc`, Laravel cache/log paths | Ignored by existing rules | Package/build/cache outputs. | Re-creatable through normal setup/build. | KEEP BUT IGNORE |
| All other modified/untracked application source, migrations, seeders, views, routes, CSS, and Dart feature files shown by `git status` | Modified or untracked | Active user feature work. | Could lose work if removed or overwritten. | KEEP; protected |

No files were classified SAFE TO DELETE or MOVE/ARCHIVE. Available evidence did not justify deleting any candidate or moving local data without risking user work.

## C. YOLO Dataset and Model

- FastAPI runtime path in the current working tree is `models/firedetect-11s.pt`, loaded by `services/yolo_detector.py` through the router included from `app/main.py`.
- `yolo11n.pt` is referenced by a standalone local test script, not by the FastAPI detector path.
- `fire_risk_model.pkl` is the separate tracked fire-risk prediction model. It remains in place and is explicitly referenced by `config.py` and fire-risk code.
- Local YAML manifests declare a fire-only dataset and fire/smoke datasets. The downloaded smoke data manifest lists `smoke` and `fire`; the manifest declarations do not prove the actual contents or class mapping of the active `.pt` model.
- Model origin, license, exact byte size, recovery/download instructions, and actual `model.names` values were not verified. Inspecting the checkpoint through the normal Ultralytics/PyTorch load path may unpickle a local checkpoint, so this audit did not load it. Therefore support for both `fire` and `smoke` is **unverified**.
- YOLO dataset/model files are untracked and have not appeared in the commit history found by path inspection. The tracked risk datasets and risk model have appeared in history. The downloaded YOLO data source and backup status are unknown; no data was deleted.
- Ignore rules now exclude only YOLO `images/`, `labels/`, downloaded `train/`, `val/`, `test/`, and generated `runs/` directories under the AI service. YAML manifests and source scripts stay visible. This keeps large local training data out of new staging while preserving local files.
- Keep the active model available to deployment. Before team distribution, verify source, license, class names, size, and reproducible retrieval; then choose a documented model artifact release or Git LFS/download mechanism. Do not replace it with the generic `yolo11n.pt` without model/class validation.

## D. Git Status and Large Files

- Branch: `main`; upstream: `origin/main`; local branch reports `ahead 0, behind 6`. No fetch was run, so this is the existing remote-tracking view, not a fresh network check.
- Before edits: 24 modified tracked files, numerous untracked feature and AI files, and no staged changes. All were preserved.
- Tracked working-tree large candidates include `fire_risk_model.pkl` (path measured in `_ai_files.txt` at 1,391,487 bytes; working-tree size not independently measured) and tracked raw geospatial rasters / dataset CSVs. They are already tracked and relevant to AI workflows.
- Root `_ai.txt` is tracked at 3,509,772 bytes and has no source-code consumer located. Its actual purpose remains uncertain.
- Path history shows tracked risk model/data in commit `1d5f286` and earlier. The new YOLO dataset and `.pt` candidates are not tracked in the current index. `.gitignore` alone cannot remove already committed history objects.
- `git count-objects -vH` reported approximately 11.05 GiB loose objects and a warning for `.git/objects/pack/tmp_pack_buu7Zk` classified as 1.33 GiB garbage. This may be an interrupted Git operation; do not delete it or run `git gc` without first resolving why it exists and ensuring recovery/backup.
- No history rewrite is recommended as part of this cleanup. Since `main` is behind the remote and contains local work, inspect the six incoming commits and coordinate before merge/rebase/push. If historical large data must be removed, first agree with collaborators on a coordinated history rewrite and backups.

## E. Files Changed

- `ai_service/ai_service_sigma/.gitignore`: added precise ignores for local YOLO image/label data, downloaded dataset splits, and regenerable inference output.
- `docs/audits/SIGMA_REPOSITORY_CLEANUP.md`: added this audit report.
- No files deleted, moved, or staged; no commit or push created.

## F. Verification

- Read-only inspection run: `git status --short --branch`, `git status --porcelain=v2 --branch`, `git branch --show-current`, `git log -5 --oneline --decorate`, `git ls-files`, `git log --all -- <risk model/data paths>`, `git check-ignore -v`, and `git count-objects -vH`.
- Source references inspected with `rg`; runtime path confirmed from `app/main.py`, `services/yolo_detector.py`, and `routers/yolo_detection.py`.
- `git diff --stat` showed pre-existing user changes across 24 tracked files. No application tests, inference, Laravel commands, Flutter analysis, migration, model load, or dataset preparation was run because those paths are unrelated to the ignore/report-only cleanup and could alter or overwrite local artifacts.
- Final checks: `git diff --check` completed with no whitespace errors (Git emitted existing LF-to-CRLF warnings for three modified Dart files). `git check-ignore -v` confirmed the intended image/label, downloaded split, and `runs/` paths are ignored; the command did not match `data.yaml` or `prepare_yolo_dataset.py`, so those remain eligible for tracking. Final `git status` shows the original work plus the `.gitignore` change and new audit report; no staged paths were added.
- No claim is made that the full application or AI inference works.

## G. Remaining Risks

- The six commits reported behind `origin/main` have not been inspected or integrated.
- The 1.33 GiB temporary pack warning may indicate an interrupted Git operation; repository integrity and full object history were not repaired or fully audited.
- Historical object sizes were not exhaustively inventoried. Existing tracked datasets/models may materially contribute to repository size.
- Active YOLO model provenance, license, model class names, deployment retrieval, and local dataset backup/training needs remain unknown.
- Root AI metadata snapshots' actual producers and use remain unknown, so they were retained for review.
- Tests were not run; runtime correctness after the user's ongoing feature changes is unverified.

## H. Safe Next Steps

1. Back up local work and inspect `git status` before any integration; review the six incoming commits before merging or rebasing.
2. Confirm the YOLO model's source/license/classes and dataset backup/training requirements with the feature owner. Document a deterministic model retrieval process before excluding the model from deployment artifacts.
3. Investigate the Git temporary pack warning and repository backup before any garbage collection or object cleanup.
4. Review the root `_ai*`, `_ports.txt`, and `_tmp_*.log` files with their creator/team before deleting or untracking them.
5. Stage only explicitly reviewed paths, run the appropriate targeted checks for the application work, review staged changes and ignored data, then coordinate push. This audit did not commit or push.
