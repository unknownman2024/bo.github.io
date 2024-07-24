<!DOCTYPE html>
<html>
<head>
  <title>Load Movies</title>
</head>
<body>
  <button id="load-movies">Load Movies</button>
  <div id="iframe-container" style="margin-top: 20px;">
    <iframe id="movie-iframe" style="width: 100%; height: 500px;" frameborder="0"></iframe>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', async () => {
      try {
        const response = await fetch('https://boxoffice24.pages.dev/movies.json');
        if (!response.ok) {
          throw new Error('Network response was not ok ' + response.statusText);
        }
        const movies = await response.json();
        let currentIndex = 0;

        document.getElementById('load-movies').addEventListener('click', () => {
          loadMoviesInIframe(currentIndex);
        });

        function loadMoviesInIframe(index) {
          if (index >= movies.length) {
            alert('All movies loaded');
            return;
          }
          const movie = movies[index];
          const url = `update.php?movie_name=${encodeURIComponent(movie.name)}&release_date=${encodeURIComponent(movie.releaseDate)}`;
          console.log('Loading movie:', movie.name, 'URL:', url);

          const iframe = document.getElementById('movie-iframe');
          iframe.src = url;

          iframe.onload = () => {
            console.log('Iframe loaded with URL:', url);
          };

          iframe.onerror = (e) => {
            console.error('Error loading iframe:', e);
          };

          // Wait for a while before loading the next movie
          setTimeout(() => loadMoviesInIframe(index + 1), 10000); // Adjust delay if necessary
        }
      } catch (error) {
        console.error('Error fetching movies:', error);
      }
    });
  </script>
</body>
</html>
