<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Summary</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .movie-summary {
            margin: 20px;
        }
        .movie {
            margin-bottom: 10px;
        }
        .movie-title {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="movie-summary" id="movieSummary"></div>

    <script>
        async function fetchMovieData() {
            const response = await fetch('data.json');
            const data = await response.json();
            return data;
        }

        function formatNumber(num, decimals = 2) {
            return (num / 10000000).toFixed(decimals) + ' CR';
        }

        function formatLakh(num, decimals = 2) {
            return (num / 100000).toFixed(decimals) + ' L';
        }

        function createMovieElement(title, gross, footfalls, shows) {
            return `
                <div class="movie">
                    <span class="movie-title">${title}</span> - Total Gross: ${formatNumber(gross)}, FF: ${formatLakh(footfalls)}, TS: ${formatLakh(shows, 1)}K
                </div>
            `;
        }

        async function displayMovies() {
            const movieData = await fetchMovieData();
            const movies = Object.keys(movieData).map(key => ({
                title: key,
                ...movieData[key]
            }));

            movies.sort((a, b) => b.totalTrackedGross - a.totalTrackedGross);

            const movieSummary = document.getElementById('movieSummary');
            movieSummary.innerHTML = movies.map(movie => createMovieElement(movie.title, movie.totalTrackedGross, movie.totalTrackedFootfalls, movie.totalTrackedShows)).join('');
        }

        displayMovies();
    </script>
</body>
</html>
