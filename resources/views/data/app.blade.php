<!DOCTYPE html>
<html>
<head>
    <title>URL Form</title>
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <style>
  body
  {
    background-image: url("https://media.tenor.com/BRDnAfz1WfsAAAAd/hacker.gif");
  }
  </style>
</head>
<body>

<!-- Card -->
<div class="container-fluid">
<div class="card mt-5">
  <div class="card-body text-center">
<img src="https://txt.1001fonts.net/img/txt/b3RmLjgwLjAwMDAwMC5SR0YwWVNBZ0lGTmpjbUZ3WlhJLC4w/mr-monstar.regular.webp" />
  </div>
  <div class="card-body text-center">
    <form method="POST" action="{{ route('data.index') }}">
        @csrf
        <label for="urls">Enter URL:</label><br>
        <textarea id="urls" name="urls" rows="5" cols="50"></textarea>
        <br>
        <button class ="btn btn-primary" type="submit">Submit</button>
    </form>
  </div>
</div>

  
</div>

</body>
</html>
