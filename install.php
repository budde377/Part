<?php


if(isset($_GET['exec'])){
    header('Content-type: text/html; charset=utf-8');
    header("Content-Encoding: none");
    ob_start();


    function execCommand($command){



    }

    function cloneGit($address)
    {

        execCommand("git clone ".escapeshellarg($address));
    }

    switch ($_GET['exec']) {
        case "CloneGit":
            cloneGit($_POST['command']);
            break;

    }

    ob_end_flush();

} else {



?><!DOCTYPE html>
<html>
<head lang="en">
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,300' rel='stylesheet' type='text/css'>
    <meta charset="UTF-8">
    <title>Install part</title>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <style type="text/css">

        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 15px;
        }

        header {
            margin: 20px 0;
        }

        header img {
            width: 20%;
            display: block;
            margin: auto;
            max-height: 200px;
        }

        main {
            max-width: 800px;
            margin: auto;
        }

        section {
            padding: 20px 0;
            position: relative;
            -webkit-transition: opacity 0.2s;
            -moz-transition: opacity 0.2s;
            -ms-transition: opacity 0.2s;
            -o-transition: opacity 0.2s;
            transition: opacity 0.2s;
        }

        section.inactive {
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            opacity: 0.3;
        }

        section.inactive:after {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
        }

        label {
            display: block;
            line-height: 2em;
            font-style: italic;
        }

        input:not([type=submit]) {
            width: 96%;
            padding: 1% 2%;
            line-height: 2em;
            border: 0;
            outline: 1px solid #aaa;
        }

        input[type=submit] {
            padding: 1% 0;
            line-height: 2em;
            border: 0;
            width: 100%;
            margin-top: 20px;
            max-width: 300px;
            font-weight: bold;
            color: #666;
            -webkit-transition: color, background 0.2s, 0.2s;
            -moz-transition: color, background 0.2s, 0.2s;
            -ms-transition: color, background 0.2s, 0.2s;
            -o-transition: color, background 0.2s, 0.2s;
            transition: color, background 0.2s, 0.2s;
        }

        form.valid input[type=submit] {
            color: #ffffff;
            background-color: #307fa8;
        }

        h1, h2 {
            font-family: 'Open Sans', Arial, Helvetica, sans-serif;
            font-weight: normal;
        }

        h1 {
            font-size: 2em;
        }

        h2 {
            font-size: 1.4em;
        }

        p {
            font-size: 1em;
        }


    </style>
</head>
<body>
<main>
    <header>
        <img
            src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNi4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB3aWR0aD0iMzg3LjE1NnB4IiBoZWlnaHQ9IjUzNi4zMnB4IiB2aWV3Qm94PSIwIDAgMzg3LjE1NiA1MzYuMzIiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDM4Ny4xNTYgNTM2LjMyIg0KCSB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxnPg0KCTxwb2x5Z29uIGZpbGw9IiNFOTc2MjQiIHBvaW50cz0iMjE3Ljc4MSwxOTkuMjcyIDE5My41ODQsMTg1Ljk4MyAwLDI5Mi4yNTUgMTkzLjU4NCwzOTguNTQ0IDM4Ny4xNTYsMjkyLjI1NSAzNjIuOTU5LDI3OC45ODEgDQoJCTE5My41ODQsMzcxLjk3OCA0OC4zOTUsMjkyLjI1NSAJIi8+DQoJPHBhdGggZmlsbD0iIzM0ODk5MiIgZD0iTTEwOC44OTQsMTM5LjQ5MUw4NC42OSwxNTIuNzc5TDAsMTk5LjI3MmwxOTMuNTg0LDEwNi4yNzRsMTkzLjU3Mi0xMDYuMjc0bC04NC42OS00Ni40OTNsLTI0LjE5Ny0xMy4yODgNCgkJbC04NC42ODUsNDYuNDkzTDEwOC44OTQsMTM5LjQ5MXogTTI3OC4yNjksMTY2LjA1NGw2MC40OTMsMzMuMjE4bC0xNDUuMTc4LDc5LjcwOUw0OC4zOTUsMTk5LjI3Mmw2MC40OTktMzMuMjE4bDg0LjY5LDQ2LjUwNw0KCQlMMjc4LjI2OSwxNjYuMDU0eiIvPg0KCTxwb2x5Z29uIGZpbGw9IiNFODQyNDIiIHBvaW50cz0iMjE3Ljc4MSwxMy4yODkgMTkzLjU4NCwwIDAsMTA2LjI3NSAxOTMuNTg0LDIxMi41NjEgMzg3LjE1NiwxMDYuMjc1IDM2Mi45NTksOTIuOTgzIA0KCQkxOTMuNTg0LDE4NS45ODMgNDguMzk1LDEwNi4yNzUgCSIvPg0KPC9nPg0KPGc+DQoJPHBhdGggZmlsbD0iIzM1MzUzNSIgZD0iTTg1LjE4LDQ1NS4wMjFoNDMuMzU5YzcuMjI2LDAsMTAuODQsMy42MTMsMTAuODQsMTAuODR2MzcuOTM5YzAsNy4yMjgtMy42MTQsMTAuODQtMTAuODQsMTAuODRIOTYuMDINCgkJbDUuNDItMTYuMjZoMjEuNjh2LTI3LjFIOTAuNnY2NS4wMzlINzQuMzR2LTcwLjQ1OUM3NC4zNCw0NTguNjM1LDc3Ljk1Myw0NTUuMDIxLDg1LjE4LDQ1NS4wMjF6Ii8+DQoJPHBhdGggZmlsbD0iIzM1MzUzNSIgZD0iTTE1MC4yMTksNDg3LjU0MWMwLTcuMjI3LDMuNjEtMTAuODQsMTAuODMxLTEwLjg0aDQzLjMyM2M3LjIyMSwwLDEwLjgzMSwzLjYxMywxMC44MzEsMTAuODR2NDguNzc5DQoJCWgtMTYuMjA2bC0wLjA1NC00My4zNTloLTMyLjQ2NXYyNy4xaDI3LjA2M2wtNS40MTYsMTYuMjZoLTI3LjAyMmMtNy4yNTcsMC0xMC44ODUtMy42MTItMTAuODg1LTEwLjg0VjQ4Ny41NDF6Ii8+DQoJPHBhdGggZmlsbD0iIzM1MzUzNSIgZD0iTTIyNi4wOTgsNTM2LjMydi00OC43NzljMC03LjIyNywzLjYxMy0xMC44NCwxMC44NC0xMC44NGgzMi40NjZsLTUuNDIsMTYuMjZoLTIxLjYyNnY0My4zNTlIMjI2LjA5OHoiLz4NCgk8cGF0aCBmaWxsPSIjMzUzNTM1IiBkPSJNMjkxLjEzNyw0NjUuODYxdjEwLjg0aDEwLjg0bC01LjQyLDE2LjI2aC01LjQydjI3LjFoMTAuODR2MTYuMjZoLTE2LjI2Yy03LjIyNywwLTEwLjg0LTMuNjEyLTEwLjg0LTEwLjg0DQoJCXYtNTkuNjE5SDI5MS4xMzd6Ii8+DQo8L2c+DQo8L3N2Zz4NCg=="/>
    </header>
    <section id="CloneGit">
        <h1>
            Install from git
        </h1>

        <p>
            Provide an address for git to clone from.
        </p>

        <form>
            <label>
                Git clone URL
                <input name="git_address"/>
            </label>

            <input type="submit" value="Clone"/>
        </form>
    </section>

    <section id="Config" class="inactive">
        <h1>Configure website</h1>

        <p>
            Add secrets and stuff to your config.
        </p>

        <h2>
            Owner info
        </h2>

        <p>
            Configure the owner of the site. A root user account with the password "<i>password</i>" is created.
        </p>

        <form>
            <label>
                Root name
                <input type="text"/>
            </label>
            <label>
                Username
                <input type="text"/>
            </label>
            <label>
                E-mail
                <input type="email"/>
            </label>
            <label>
                Domain
                <input type="url"/>
            </label>

            <h2>
                MySQL credentials
            </h2>

            <p>
                Provide database information.
            </p>
            <label>
                Host
                <input type="text"/>
            </label>
            <label>
                Database name
                <input type="text"/>
            </label>
            <label>
                Username
                <input type="text"/>
            </label>
            <label>
                Password
                <input type="password"/>
            </label>

            <h2>
                Mail MySQL credentials
            </h2>

            <p>
                Provide credentials for the mail MySQL database
            </p>

            <label>
                Host
                <input type="text"/>
            </label>
            <label>
                Database name
                <input type="text"/>
            </label>
            <label>
                Username
                <input type="text"/>
            </label>
            <label>
                Password
                <input type="password"/>
            </label>


            <h2>
                Facebook credentials
            </h2>

            <p>
                Provide facebook app secret and id.
            </p>
            <label>
                App id
                <input type="text"/>
            </label>
            <label>
                Secret
                <input type="text"/>
            </label>
            <label>
                Permanent token
                <input type="text"/>
            </label>
            <input type="submit" value="Save"/>
        </form>


    </section>
</main>
<script type="text/javascript">

    function nonEmptyValueValidator(element) {
        return $(element).val().trim() != "";
    }


    function execGit() {

    }


    function exec(response_handler, done_handler, error_handler){
        var last_response_len = false;
        $.ajax('?exec=CloneGit', {
            xhrFields: {
                onprogress: function (e) {
                    var this_response, response = e.currentTarget.response;
                    if (last_response_len === false) {
                        this_response = response;
                        last_response_len = response.length;
                    }
                    else {
                        this_response = response.substring(last_response_len);
                        last_response_len = response.length;
                    }
                    if(response_handler != undefined){
                        response_handler(this_response);
                    }
                    console.log(this_response);
                }
            }
        })
            .done(function (data) {
                if(done_handler != undefined){
                    done_handler(data);
                }
                console.log('Complete response = ' + data);
            })
            .fail(function (data) {
                if(error_handler!= undefined){
                    error_handler(data);
                }

                console.log('Error: ', data);
            });
        console.log('Request Sent');

    }

    function ConsoleUpdater(pre){
        this.pre = pre;
        this.appendString = function (string){
            pre.text(pre.text()+string);
        }
    }

    function FormHandler(form) {
        this.form = form;
        var validators = [];
        var self = this;
        this.addValidator = function (element, func) {

            validators.push(function () {
                return func(element);
            });
        };

        this.isValid = function () {
            for (var func_key in validators) {
                if (!(validators[func_key]())) {
                    return false;
                }
            }

            return true;
        };

        this.updateValidClass = function () {
            $(form).toggleClass("valid", self.isValid());
        };

        this.updateOnChange = function () {
            $(form).on('input', (function () {
                self.updateValidClass();
            }));
        };
    }

    var fh = new FormHandler($("#CloneGit").find("form"));

    fh.addValidator($("input[name=git_address]", fh.form), nonEmptyValueValidator);
    fh.updateOnChange();
    fh.form.dblclick(function () {
        execGit();
    });
</script>

</body>
</html>

<?php

}