<?php

if (basename(__FILE__) == 'install.php') {
    if (count($list = glob('install_*.php'))) {
        $new_name = $list[0];
    } else {
        $new_name = uniqid('install_') . '.php';
        copy(__FILE__, $new_name);
    }

    //header("Location: $new_name");
    //die;
}

function hasGit()
{
    return file_exists('.git') && hasSiteConfigDist();
}

function hasSiteConfig()
{
    return file_exists('site-config.xml');
}

function hasSiteConfigDist()
{
    return file_exists('site-config.xml.dist');
}

function execCommand($command)
{

    $process = proc_open($command . "", [1 => ['pipe', 'w']], $pipes, null, ['HOME' => '/home/www-data']);
    error_log(isset($pipes[1]));
    $handle = $pipes[1];
    $out = "";
    while (!feof($handle)) {
        $out .= $buffer = fgets($handle);
        error_log($buffer);
        $buffer = str_pad($buffer, 2048, " ");
        echo "$buffer";
        ob_flush();
        flush();
    }
    proc_close($process);
    return $out;
}

function cloneGit($address)
{
    execCommand("git init .  2>&1 && git remote add -t \\* -f origin " . escapeshellarg($address) . "  2>&1 && git checkout master 2>&1 && curl -sS https://getcomposer.org/installer | php 2>&1 && php composer.phar install 2>&1 && echo 'success'");

}


if(isset($_GET['exec'])){
    header('Content-type: text/html; charset=utf-8');
    header("Content-Encoding: none");
    ob_start();


    switch ($_GET['exec']) {
        case "cloneGit":
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

        section:not(.inactive) form.valid input[type=submit] {
            color: #ffffff;
            background-color: #307fa8;
        }

        pre {
            font-size: 0.8em;
            color: #fff;
            background: #194E6E;
            padding: 20px 10px;
            -webkit-transition: background-color 0.1s;
            -moz-transition: background-color 0.1s;
            -ms-transition: background-color 0.1s;
            -o-transition: background-color 0.1s;
            transition: background-color 0.1s;
            max-height: 33em;
            overflow: auto;
        }

        pre.error {
            background-color: #A62424;
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
    <section id="CloneGit" class="inactive"  <?php echo hasGit() ? "hidden" : ""; ?>>
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

    <section id="ConfigMySQL" class="inactive" <?php echo hasGit() && hasSiteConfig() ? "hidden" : ""; ?> >
        <h1>Configure MySQL database</h1>


        <p>
            Provide database information.
        </p>

        <form>

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
            <input type="submit" value="Save"/>
        </form>
        </section>
    <section class="inactive">

        <h1>
            Mail MySQL credentials
        </h1>

        <p>
            Provide credentials for the mail MySQL database
        </p>
        <form>
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
            <input type="submit" value="Save"/>

        </form>


        </section>
    <section class="inactive">
        <h1>
            Facebook credentials
        </h1>
        <p>
            Provide facebook app secret and id.
        </p>
        <form>

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


    function execCloneGit(url, response_handler, done_handler, error_handler) {
        exec({command: url}, 'cloneGit', response_handler, done_handler, error_handler);
    }


    function exec(data, action, response_handler, done_handler, error_handler) {
        var last_response_len = false;
        $.ajax('?exec=' + action, {
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

                    this_response = this_response.split("\n").map(function (substring) {
                        return substring.trim();
                    }).join("\n");


                    if (response_handler != undefined) {
                        response_handler(this_response);
                    }
                }
            },
            type: 'POST',
            data: data
        })
            .done(function (data) {
                if (done_handler != undefined) {
                    done_handler(data);
                }
            })
            .fail(function (data) {
                if (error_handler != undefined) {
                    error_handler(data);
                }

            });

    }

    function ConsoleUpdater(pre) {
        this.pre = pre;
        var self = this;
        this.appendString = function (string) {
            pre.text(pre.text() + string);
            pre.scrollTop(pre[0].scrollHeight);
        };

        this.markError = function () {
            pre.addClass('error');
        };

        this.markNonError = function () {
            pre.removeClass('error');
        };

        this.toggleError = function (bool) {
            if (bool == undefined) {
                self.toggleError(!pre.hasClass('error'));
                return;
            }

            if (bool) {
                self.markError();
            } else {
                self.markNonError();
            }

        };

        this.clear = function () {
            pre.text("");
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
        var submitHandler = function () {
            return false;
        };
        this.setSubmitHandler = function (handler) {
            submitHandler = handler;
        };

        form.submit(function (event) {
            event.preventDefault();
            if (!self.isValid()) {
                return;
            }
            submitHandler(self);
        });
    }

    function SectionHandler(sections) {
        this.sections = sections;

        var pre = $("<pre></pre>");
        var cons = new ConsoleUpdater(pre);
        var section_num = 0;
        var section = undefined;

        var checks = [];
        var handlers = [];
        var self = this;
        this.nextSection = function () {
            if (sections.length <= section_num) {
                return;
            }

            section = $(sections[section_num]);
            pre = $("<pre></pre>");
            cons = new ConsoleUpdater(pre);



            if (section_num > 0) {
                $('html, body').animate({
                    scrollTop: section.offset().top
                });
            }
            section_num++;

            self.activate();


            for (var i in checks) {
                if (checks[i](section)) {
                    handlers[i](section);
                    return;
                }
            }

        };
        this.getCurrentSection = function () {
            return section;
        };

        this.getConsole = function () {
            pre.insertAfter(section);
            return cons;
        };

        this.deactivate = function () {
            section.addClass('inactive');
        };

        this.activate = function () {
            section.removeClass('inactive');
        };

        this.addHandler = function (check_function, handler) {
            checks.push(check_function);
            handlers.push(handler);
        }
    }

    var sectionHandler = new SectionHandler($("main > section:not(:hidden)"));
    sectionHandler.addHandler(
        function (section) {
            return section.attr('id') == "CloneGit";
        },
        function (section) {
            var fh = new FormHandler(section.find("form"));
            var git_address_input = $("input[name=git_address]", fh.form);
            fh.addValidator(git_address_input, nonEmptyValueValidator);
            fh.updateOnChange();

            fh.setSubmitHandler(function () {
                var cu = sectionHandler.getConsole();
                cu.clear();
                cu.markNonError();
                sectionHandler.deactivate();
                execCloneGit(
                    git_address_input.val(),
                    cu.appendString,
                    function (data) {
                        var success = is_success(data.trim());
                        cu.toggleError(!success);
                        if (!success) {
                            sectionHandler.activate();
                        } else {
                            sectionHandler.nextSection();
                        }

                    });
            });
        });

    sectionHandler.nextSection();
    $('body').dblclick(function () {
        sectionHandler.nextSection();
    });

    function is_success(data) {
        return data.substr(data.length - 7) == "success";
    }
</script>

</body>
</html>

<?php

}