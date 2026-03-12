Set-Location "$PSScriptRoot/apps/backend-fastapi"

if (!(Test-Path .venv)) {
  py -m venv .venv
}

.\.venv\Scripts\python -m pip install -r requirements.txt
.\.venv\Scripts\python -m uvicorn app.main:app --reload --port 8000
