<html>

<head>
  <title><?= $title ?></title>
</head>

<body>
  <h1><?= $title ?></h1>
  <form method="post" action="addperson">
    <label for="name">Имя</label>
    <input required name="name" type="text" />
    <label for="surname">Фамилия</label>
    <input required name="surname" type="text" />
    <input type="submit" />
  </form>
</body>

</html>