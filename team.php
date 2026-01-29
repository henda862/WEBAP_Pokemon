<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pokemon Basics</title>
    <link rel="stylesheet" href="css/main.css">
    <link href="https://fonts.cdnfonts.com/css/g-guarantee" rel="stylesheet">
    <script type="text/javascript" src="js/code.jquery.com_jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="js/code.jquery.com_ui_1.13.2_jquery-ui.js"></script>
</head>
<body>
<header>
    <img class="logo" src="img/logo.png" alt="Pokemon">
    <h1>3. Ajax dynamic content Exercises</h1>
</header>
<?php
// Check if the session field "id" is set.
// If not, kick the user out to the login Page.
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}
?>
<nav style="display: none;">
    <ul>
        <li><a id="logout" href="#">Logout</a></li>
        <li><a href="team.php">My Team</a></li>
        <li><a href="explore.php">Explore</a></li>
        <li><a href="arena.php">Arena</a></li>
        <li><a href="pokedex.php">Pokedex</a></li>
        <li><a href="chatroom/index.php">Chatroom</a></li>
    </ul>
</nav>
<main style="display: none;">
    <h2>Exercise 2: Loading Items from a database</h2>
    <h3>My Team</h3>
    <div id="pokemonDataDiv" class="flexed"></div>
    <script>
        // Exercise 3 Instructions:
        // 1. Show the "nav" on load with a fitting effect.
        // 2. If the user clicks on Logout, log the User out and send him to the index page
        // 3. Load the Team data from getTeam.php directly on document ready.
        // 4. When finished show the "main" on load with a fitting effect.

        $(document).ready(function() {
            // 1. Show the "nav" on load with a fitting effect
            $("nav").fadeIn(500);

            // 2. If the user clicks on Logout, log the User out and send him to the index page
            $("#logout").on("click", function(e) {
                e.preventDefault();
                $.get("php/doLogout.php")
                    .always(function() {
                        window.location.replace("index.php");
                    });
            });

            // 3. Load the Team data from getTeam.php directly on document ready
            loadTeamData();
        });

        function loadTeamData() {
            $.ajax({
                url: 'php/getTeam.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        displayTeam(response.pokemon);
                    } else {
                        $("#pokemonDataDiv").html('<p>Error loading team: ' + response.message + '</p>');
                    }
                    // 4. When finished show the "main" on load with a fitting effect
                    $("main").fadeIn(500);
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 401) {
                        // User not authenticated, redirect to login
                        window.location.replace("index.php");
                    } else {
                        $("#pokemonDataDiv").html('<p>Error loading team data. Please try again.</p>');
                        $("main").fadeIn(500);
                    }
                }
            });
        }

        function displayTeam(pokemonList) {
            var container = $("#pokemonDataDiv");
            container.empty();

            if (pokemonList.length === 0) {
                container.html('<p>You have no Pokemon in your team yet. Go explore to find some!</p>');
                return;
            }

            for (var i = 0; i < pokemonList.length; i++) {
                var poke = pokemonList[i];
                var healthPercent = poke.health;

                var sectionHtml = '<section class="section">' +
                    '<h2>Name: ' + escapeHtml(poke.nickname) + '</h2>' +
                    '<p>Level: ' + poke.level + '</p>' +
                    '<img src="assets/pokedata/thumbnails/' + poke.formattedSpeciesId + '.png" alt="' + escapeHtml(poke.speciesName) + '">' +
                    '<div class="pokemon-health">' +
                    '<div class="health-bar">' +
                    '<div class="current-health ' + poke.healthClass + '" style="width:' + healthPercent + '%">' +
                    '<div class="health-text"><span class="current-hp">' + poke.health + '</span>/<span class="max-hp">100</span> HP</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</section>';

                container.append(sectionHtml);
            }
        }

        // Helper function to escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</main>
</body>

</html>