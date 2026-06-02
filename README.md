## Installation Pre-requisites
<p>You need to have these installed in your machine to be able to install & run Laravel.</p>
<ol>
    <li>XAMPP (with PHP 7.3.8 or newer)</li>
    <li>Composer</li>
    <li>Git</li>
    <li>Visual Studio Code (This is optional but is preffered)</li>
    <li>NPM (Node Package Manager)</li>
</ol>

## Project Installation

<b><u>Step 1</u></b>:
<p>Clone the project into your machine, by executing this command but make sure you have Git installed.</p>

```
git clone https://github.com/carlomigueldy/EMR
```

<b><u>Step 2</u></b>
<p>After cloning the project, go to the project directory by executing this command.</p>

```
cd EMR
```

<b><u>Step 3</u></b>
<p>Once inside the project directory, you need to install the project's dependencies. Execute this command,</p>

```
composer install
```

<b><u>Step 4</u></b>
<p>After dependencies are installed, setup an .env file by simple copying the .env.example file. Execute this command.</p>

```
cp .env.example .env
```

<b><u>Step 5</u></b>
<p>After copying the file, set up the following configurations for your Database setup.</p>

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=<your_database_name>
DB_USERNAME=<your_database_username>
DB_PASSWORD=<your_database_password>
```

<b><u>Step 6</u></b>
<p>Save the file after setting it up. Then run this command to generate a key.</p>

```
php artisan key:generate
```

<b><u>Step 7</u></b>
<p>After all the neccessary configurations, you can now run a migration. Make sure you have your Apache & MySQL server turned on, then simply execute this command in the terminal.</p>

```
php artisan migrate:fresh --seed
```

<b><u>Step 8</u></b>
<p>Then host a local web server by executing this command.</p>

```
php artisan serve --port=50
```

## Collaborative Development utilizing Git

<dl>
  <p>We are constantly writing code and make changes and so on. And this repository is our single code base. We will be using Git a lot during development, so here are my instructions for using Git for our collaborative development for the EMR System.</p>
  <dt>I have implemented several features for the system. How do I push this to the main repository?</dt>
  <dd>
      <p>Stage the changes and commit it along with a relevant commit message.</p>
      <div>Stage the changes by executing the command below.</div>
      <code>git add .</code>
      <div>Commit it with a relevant commit message. (e.g. implemented User create form)</div>
      <code>git commit -m 'I have implemented something'</code>
      <div>After committing changes, simply push those changes into the repository</div>
      <code>git push</code>
  </dd>
</dl>
