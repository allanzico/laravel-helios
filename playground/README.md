# Helios Playground

This is a local Laravel app for testing the package in a real host project.

The app installs `allanzico/laravel-helios` through a Composer path repository pointing to `../`, so package edits are reflected here immediately.

## Run

```bash
cd playground
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8001
```

Open:

- `http://127.0.0.1:8001` for demo links
- `http://127.0.0.1:8001/helios` for the Helios dashboard

## Demo Data

Use the homepage links to generate requests, logs, queries, errors, and a failed queue job.

The scheduled command `demo:heartbeat` is registered in `routes/console.php` and allowlisted for manual runs in `.env.example`.
