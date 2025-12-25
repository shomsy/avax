# 💎 Avax Database: The Super Easy Guide! 💎

(Even your pet hamster could understand this! 🐹)

Welcome, friend! You are about to learn how to use **Avax**, a magical tool that helps you build and manage your
database without breaking a sweat (or your code).

---

## 🏗️ What are Migrations?

Imagine you are building a giant LEGO castle. 🧱
Instead of just putting bricks together and forgetting how you did it, you write down **instructions** for every room
you build.

* **Migrations** are those instruction papers.
* They tell the computer: "Hey! Please make a table for 'Users' with a 'Nickname' and an 'Email'."
* If you make a mistake, you can just look at the paper and undo it!

---

## 🛠️ The Magical `avax` Command

Everything happens in your terminal (that black box where you type stuff). Just type `php avax` to see the magic menu!

### 1. 📝 Making a New Instruction (Making a Migration)

Want to build a new table?

```bash
php avax make create_users_table --create=users
```

This creates a new file. Open it, and you'll see a place to list what columns you want (like name, age, or favorite
pizza).

### 2. 🚀 Building the Castle (Running Migrations)

Finished writing your instructions? Tell Avax to build it!

```bash
php avax migrate
```

Avax reads all your new papers and builds the tables in the database. Boom! Done! 💥

### 3. ⏪ Time Travel (Rollback)

Oops! Did you accidentally name a table "Cucumber" instead of "Customer"? 🥒

```bash
php avax rollback
```

Avax will "undo" the last thing you built. It’s like a giant "Ctrl+Z" for your database!

### 4. 🔍 The "Pretend" Mode (Dry Run)

Are you scared that your changes might explode? Use the pretend mode! 🕵️‍♂️

```bash
php avax migrate --dry
```

Avax will show you exactly what it **would** do, without actually doing it. It's like trying on clothes before you buy
them.

---

## 🛡️ Enterprise Superpowers (The Cool Stuff)

### 🕵️‍♀️ The Secret Seal (Integrity Check)

Avax is smart. Every time it builds something, it puts a secret "seal" (a hash) on it. If someone Sneaky Steve 🧔 changes
an old migration file later, Avax will shout: **"WAIT! Someone changed the instructions! I'm not moving until you fix
this!"**

### ⚠️ The Safety Net (Confirmations)

If you try to delete something big, Avax will stop and ask: **"Are you sure? Really REALLY sure?"** 🛑
You have to type `y` to say "Yes, I know what I'm doing!"

---

## 🗄️ Managing the Database & Tables

### 🌟 Database Tools

* `db:create my_game` — Makes a brand new playground (database).
* `db:drop my_game` — Blows up the playground. Be careful! 🧨
* `db:export` — Takes a "photo" (snapshot) of your database so you can keep it as a SQL file.

### 🧹 Table Cleaning

* `table:drop users` — Completely deletes the 'users' table.
* `table:truncate logs` — Empties all the data inside 'logs' but keeps the table empty and ready for new stuff.

---

## 🌱 Planting Data (Seeding)

A database with no data is like a library with no books. 📚
**Seeding** is like planting seeds that grow into data.

```bash
php avax db:seed
```

This will automatically fill your database with 100 or even 100,000 "fake" users so you can test your app and see how it
works!

---

## 🌈 Summary for Kids

1. **`make`**: Get a new blank piece of paper.
2. **`migrate`**: Build what's on the paper.
3. **`rollback`**: Undo what you built.
4. **`status`**: See which papers are already done.
5. **`db:export`**: Save your progress to a file.
6. **`db:seed`**: Add some fake people to your app.

**That's it! You are now an Avax pro! 🏆 Go build something awesome!** 🚀
