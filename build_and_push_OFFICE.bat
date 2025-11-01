@echo off

chcp 65001 > nul

cd /d "C:\OpenServer\domains\of2024"

echo Building Docker image...
docker build -t ghcr.io/andrey18051/office:1.0  .
if %ERRORLEVEL% NEQ 0 (
    echo Error building Docker image.
    exit /b 1
)
echo Docker image built successfully.

echo Pushing Docker image to GitHub...
docker push ghcr.io/andrey18051/office:1.0
if %ERRORLEVEL% NEQ 0 (
    echo Error pushing Docker image.
    exit /b 1
)
echo Docker image pushed to GitHub successfully.

