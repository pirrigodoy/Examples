# M07 DWES WebApp README.md
- Pierre Godoy Ccori - Exam
-------------------------------------------------------------------------------

# About
- This is an WebApp example for M07 DWES.

# Project dir structure
- db:      All data files: databases, sessions (context), .csv files, etc. 
- public:  All static content: img, css, js, etc.
- src:     Contains the app code and its libraries
- src/app: The main codebase that generates the dynamic content.
- src/lib: First-party libraries needed by the app. Not managed by composer.
- tests:   Tests of code in src. Done with Pest.
- vendor:  Third-party dependencies. Managed by Composer.

# Project main files
- composer.json: List of project dependencies managed by Composer. Can be manually edited.
- composer.lock: List of all dependencies managed by Composer. Generated automatically.
- phpunit.xml:   PHPUnit config file. Used by Pest.
- README.md:     This file.

# Source
- Start reading src/app/rewriter.php

# Deployment
- Copy the whole directory containing this file wherever you want.

# Execution with PHP's Development Web Server
- php -S 0.0.0.0:8080 -t public/ src/app/rewriter.php

# Execution with a production server (Apache, Ngnix, Caddy)
- Configure URL Rewriting.
- Point the web server to execute src/app/router.php.



# Debugging
-------------------------------------------------------------------------------

## VSCode
1. Open debug panel to the left.
2. Generate and open .vscode/launch.json
3. Append the following code to the "configurations" array:

        {
            "name": "Launch Rewriter",
            "type": "php",
            "request": "launch",
            "runtimeArgs": [
                "-dxdebug.mode=debug",
                "-dxdebug.start_with_request=yes",
                "-S",
                "localhost:8080",
                "-t",
                "${fileDirname}/../../public",
                "${file}"
            ],
            "program": "",
            "cwd": "${workspaceRoot}",
            "port": 9003,
            "serverReadyAction": {
                "pattern": "Development Server \\(http://localhost:([0-9]+)\\) started",
                "uriFormat": "http://localhost:%s",
                "action": "openExternally"
            }
        }

4. Save .vscode/launch.json
5. Open again the VSCode debug panel in the left.
   At the top of the panel there is a list of four options.
   - Launch currently open script: Runs a script in a terminal.
   - Launch Rewriter: Launches a webapp in PHP's development server.
   Choose "Launch Rewriter", but don't press the "Play" button.
6. Open the file src/app/rewriter.php and press F5.
7. The debugger will start.
   You can add breakpoints and leave the debugger running while you change code.
   Refresh the browser to see changes.



## Browser
1. Press F12 to open the Developer Tools.
2. Click on the 'Network' Tab.
3. Check 'Disable Cache'. (Very important!)

-------------------------------------------------------------------------------
