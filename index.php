<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Basic Computer Assembler</title>
    <!-- Bootstrap core CSS-->

    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom fonts for this template-->
    <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <!-- Page level plugin CSS-->
    <link rel="stylesheet" href="css/hacker.css">
    <!--    <link href="css/sb-admin.css" rel="stylesheet">-->


</head>

<body>
<a href="https://github.com/kidusmakonnen/mano-assembler" class="github-corner" aria-label="View source on GitHub">
    <svg width="80" height="80" viewBox="0 0 250 250"
         style="z-index:1; fill:#151513; color:#fff; position: absolute; top: 0; border: 0; right: 0;"
         aria-hidden="true">
        <path d="M0,0 L115,115 L130,115 L142,142 L250,250 L250,0 Z"></path>
        <path d="M128.3,109.0 C113.8,99.7 119.0,89.6 119.0,89.6 C122.0,82.7 120.5,78.6 120.5,78.6 C119.2,72.0 123.4,76.3 123.4,76.3 C127.3,80.9 125.5,87.3 125.5,87.3 C122.9,97.6 130.6,101.9 134.4,103.2"
              fill="currentColor" style="transform-origin: 130px 106px;" class="octo-arm"></path>
        <path d="M115.0,115.0 C114.9,115.1 118.7,116.5 119.8,115.4 L133.7,101.6 C136.9,99.2 139.9,98.4 142.2,98.6 C133.8,88.0 127.5,74.4 143.8,58.0 C148.5,53.4 154.0,51.2 159.7,51.0 C160.3,49.4 163.2,43.6 171.4,40.1 C171.4,40.1 176.1,42.5 178.8,56.2 C183.1,58.6 187.2,61.8 190.9,65.4 C194.5,69.0 197.7,73.2 200.1,77.6 C213.8,80.2 216.3,84.9 216.3,84.9 C212.7,93.1 206.9,96.0 205.4,96.6 C205.1,102.4 203.0,107.8 198.3,112.5 C181.9,128.9 168.3,122.5 157.7,114.1 C157.9,116.9 156.7,120.9 152.7,124.9 L141.0,136.5 C139.8,137.7 141.6,141.9 141.8,141.8 Z"
              fill="currentColor" class="octo-body"></path>
    </svg>
</a>
<style>.github-corner:hover .octo-arm {
        animation: octocat-wave 560ms ease-in-out
    }

    @keyframes octocat-wave {
        0%, 100% {
            transform: rotate(0)
        }
        20%, 60% {
            transform: rotate(-25deg)
        }
        40%, 80% {
            transform: rotate(10deg)
        }
    }

    @media (max-width: 500px) {
        .github-corner:hover .octo-arm {
            animation: none
        }

        .github-corner .octo-arm {
            animation: octocat-wave 560ms ease-in-out
        }
    }</style>
<div class="card-body">
    <div class="row">
        <div class="col-lg-3">
            <div class="panel panel-primary">
                <div class="panel-heading"><h3 class="panel-title">Assembly Code</h3>
                    <label>Sample Code: </label>
                    <button class="btn btn-primary" onclick="fill(1)">1</button>
                    <button class="btn btn-primary" onclick="fill(2)">2</button>
                    <button class="btn btn-primary" onclick="fill(3)">3</button>
                </div>
                <div class="panel-body">
                    <textarea id="asm" class="form-control input-lg" rows=12"></textarea>
                </div>
            </div>
            <span id="ast"></span>
        </div>
        <div class="col-lg-5">
            <h3>First Pass</h3>
            <span id="first"></span>
        </div>
        <div class="col-lg-4">
            <h3>Second Pass</h3>
            <span id="second"></span>
        </div>
    </div>
    <div class="row">
    </div>
</div>
<div class="card-footer small text-muted text-right">
    <a class="btn btn-primary" href="#" id="btnAssemble"
       onclick="go()">Go</a>
    <a class="btn btn-primary" href="download.php" id="btnDownload" hidden="true">Download Object File <i
                class="fa fa-fw fa-download"></i></a>
</div>
<div class="card-footer small text-muted text-center">A 2 pass assembler for the Morris Mano basic computer implemented
    in PHP. <a href="https://github.com/kidusmakonnen/mano-assembler">Source</a></div>


<!-- Bootstrap core JavaScript-->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Core plugin JavaScript-->
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<!-- Page level plugin JavaScript-->
<!-- <script src="vendor/datatables/dataTables.bootstrap4.js"></script> -->
<!-- Custom scripts for all pages-->
<script src="js/sb-admin.min.js"></script>
<script type="text/javascript">
    function go() {
        $('#btnDownload').attr('hidden', true);
        $('#first').html('');
        $('#second').html('');
        $('#ast').html('');

        var asm = $('#asm').val();
        $.ajax({
            url: 'asm.php',
            type: 'POST',
            dataType: 'json',
            data: {src: asm},
            success: function (result) {
                var first_pass_result = result.first_pass_result.output;
                var ast = result.first_pass_result.label_addresses;
                var second_pass_result = result.output;

                var first = "<table class='table table-bordered table-striped table-hover'><thead><tr><th>Memory Word</th><th>Symbol</th><th>Hex</th><th>Binary representation</th></tr></thead><tbody>";
                for (var i = 0; i < first_pass_result.length; i++) {
                    first += "<tr><td>" + (i + 1) + "</td>";
                    if (first_pass_result[i].length == 3) {
                        first += "<td>" + first_pass_result[i][0] + "</td>";
                        first += "<td>" + first_pass_result[i][1] + "</td>";
                        first += "<td>" + first_pass_result[i][2] + "</td>";
                    } else {
                        first += "<td>(LC)</td>";
                        first += "<td>" + first_pass_result[i][0] + "</td>";
                        first += "<td>" + first_pass_result[i][1] + "</td>";
                    }
                }
                first += "</tr></tbody></table>";
                $('#first').html(first);

                var second = "<table class='table table-bordered table-striped table-hover'><thead><tr><th>Address</th><th>Machine Code</th></tr></thead><tbody>";
                for (var i = 0; i < second_pass_result.length; i++) {
                    second += "<tr><td>" + second_pass_result[i][0] + "</td>";
                    second += "<td>" + second_pass_result[i][1] + "</td>";
                }
                second += "</tr></tbody></table>";
                $('#second').html(second);

                var ast_table = "<strong>Address Symbol Table</strong><table class='table table-bordered table-striped table-hover'><thead><tr><th>Hexadecimal Address</th><th>Address Symbol</th></tr></thead><tbody>";
                for (var i = 0; i < ast.length; i++) {
                    ast_table += "<tr><td>" + ast[i][1] + "</td>";
                    ast_table += "<td>" + ast[i][0] + "</td>";
                }
                ast_table += "</tr></tbody></table>";

                $('#ast').html(ast_table);
                $('#btnDownload').attr('hidden', false);
            }
        });
    }

    function fill(sample) {
        var sampleCode = "";
        switch (sample) {
            case 1:
                sampleCode = "ORG 100\n" +
                    "LDA SUB\n" +
                    "CMA\n" +
                    "INC\n" +
                    "ADD MIN\n" +
                    "STA DIF\n" +
                    "HLT\n" +
                    "MIN, DEC 83\n" +
                    "SUB, DEC -23\n" +
                    "DIF, HEX 0\n" +
                    "END";
                break;
            case 2:
                sampleCode = "ORG 100\n" +
                    "LDA ADS\n" +
                    "STA PTR\n" +
                    "LDA NBR\n" +
                    "STA CTR\n" +
                    "CLA\n" +
                    "LOP, ADD PTR I\n" +
                    "ISZ PTR\n" +
                    "ISZ CTR\n" +
                    "BUN LOP\n" +
                    "STA SUM\n" +
                    "HLT\n" +
                    "ADS, HEX 150\n" +
                    "PTR, HEX 0\n" +
                    "NBR, DEC -4\n" +
                    "CTR, HEX 0\n" +
                    "SUM, HEX 0\n" +
                    "ORG 150\n" +
                    "DEC 75\n" +
                    "DEC 25\n" +
                    "DEC 100\n" +
                    "DEC -1\n" +
                    "END";
                break;
            case 3:
                sampleCode = "ORG 100\n" +
                    "CLE\n" +
                    "CLA\n" +
                    "STA CTR\n" +
                    "LDA WRD\n" +
                    "SZA\n" +
                    "BUN ROT\n" +
                    "BUN STP\n" +
                    "ROT, CIL\n" +
                    "SZE\n" +
                    "BUN AGN\n" +
                    "BUN ROT\n" +
                    "AGN, CLE\n" +
                    "ISZ CTR\n" +
                    "SZA\n" +
                    "BUN ROT\n" +
                    "STP, HLT\n" +
                    "CTR, HEX 0\n" +
                    "WRD, HEX 62C1\n" +
                    "END";
                break;
        }
        $('#asm').val(sampleCode);
    }
</script>
</body>

</html>
