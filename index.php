<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Page</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      background: linear-gradient(to right, #ffffff, #2193b0);
      color: #fff;
    }

    .container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 80%;
      max-width: 1000px;
      background: transparent;
      border-radius: 8px;
    }

    .content {
      flex: 1;
      text-align: left;
      padding: 20px;
    }

    .content h1 {
      font-size: 48px;
      font-weight: bold;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
      margin-bottom: 10px;
    }

    .content p {
      font-size: 20px;
      text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
    }

    .form-container {
      flex: 1;
      background: #005377;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      text-align: center;
    }

    .form-container input {
      display: block;
      width: 90%;
      max-width: 300px;
      margin: 10px auto;
      padding: 10px;
      font-size: 16px;
      border: none;
      border-radius: 4px;
      box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .form-container button {
      display: block;
      width: 90%;
      max-width: 300px;
      margin: 10px auto;
      padding: 10px;
      font-size: 18px;
      font-weight: bold;
      color: #005377;
      background: #6dd5ed;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .form-container button:hover {
      background: #2193b0;
    }

    .form-container a {
      display: block;
      margin-top: 10px;
      font-size: 14px;
      color: #ddd;
      text-decoration: none;
    }

    .form-container a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="content">
      <h1>Login to start</h1>
      <p>Your central hub for employment</p>
    </div>
    <div class="form-container">
      <form>
        <input type="email" placeholder="you@example.com" required>
        <input type="password" placeholder="password" required>
        <button type="submit">Login</button>
        <a href="#">New here? Create an account!</a>
        <button type="button">Create account</button>
      </form>
    </div>
  </div>
</body>
</html>