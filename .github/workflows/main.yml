name: Run PHP Script

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]
  workflow_dispatch:

jobs:
  run-script:
    runs-on: ubuntu-latest

    steps:
    - name: Checkout repository
      uses: actions/checkout@v4

    - name: Set up PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: pcntl

    - name: Run PHP Script
      run: |
        echo "Starting PHP script"
        php script.php

    - name: Archive Results
      if: always()
      uses: actions/upload-artifact@v4
      with:
        name: script-results
        path: results.txt
        retention-days: 7

    - name: Print Logs
      run: |
        echo "Script completed. Check the logs above for execution details."
