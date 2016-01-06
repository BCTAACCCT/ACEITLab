<?php
/**
 * ACEITLab Application - Console Error Page
 *
 * provides interface for inactive vm remote consoles
 *
 * @author  Michael White-Webster
 * @version 0.7.3
 * @access  private
 */

/**
 * main
 */
?>
<html>
<head profile="http://www.w3.org/2005/10/profile">
    <title>ACEITLab - Console Error</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="css/rudi.css" />
    <link rel="icon" type="image/png" href="icon/favicon-32x32.png"/>
    <style>
        body, div,p {
            background-color: black;
        }
        .container {
            height: 100%;
            position: relative;
        }
        .container p {
            margin: 0;
            width: 100%;
            position: absolute;
            top: 50%;
            transform: translate(0, -50%);
            text-align: center;
            color: white;
            font-family: "Lucida Console", Monaco, monospace;
            font-size: 400%;
        }
    </style>
</head>
<body>
<div class="container">
    <p>No Connection</p>
</div>
</body>
</html>