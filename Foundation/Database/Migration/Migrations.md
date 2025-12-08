# Gemini CLI Documentation: The Complete Guide

Welcome to the **Gemini CLI Documentation**! This guide is designed to explain every feature and command in the Gemini
CLI. Whether you're creating individual components like migrations or entities, or combining multiple components into a
single operation, this guide will walk you through everything you need to know.

---

## **Overview**

Gemini CLI is a tool to automate the generation of common application components such as:

- **Migrations**
- **Entities**
- **DTOs**
- **Repositories**
- **Services**
- **Controllers**

You can use it to generate these components either **individually** or **together in combinations**.

---

## **Global Commands**

### **Help**

Display the help message with a list of all available commands:

```bash
php gemini --help
```

### **Version**

Check the version of the Gemini CLI:

```bash
php gemini --version
```

### **Debug**

Enable debug mode for detailed error messages:

```bash
php gemini <command> [arguments] --debug
```

---

## **Available Commands**

### **1. make:migration**

Generates a new database migration file.

**Syntax:**

```bash
php gemini make:migration <MigrationName> --table=<TableName> [--fields="<field:type,field:type>"]
```

**Examples:**

1. Basic migration:
   ```bash
   php gemini make:migration CreateUsersTable --table=users
   ```
2. Migration with fields:
   ```bash
   php gemini make:migration CreateProductsTable --table=products --fields="id:int,name:string,price:decimal"
   ```

---

### **2. make:entity**

Generates an **Entity Class** based on a table schema.

**Syntax:**

```bash
php gemini make:entity <EntityName> --fields="<field:type,field:type>"
```

**Examples:**

1. Basic entity:
   ```bash
   php gemini make:entity User --fields="id:int,name:string,email:string"
   ```
2. Entity with default field lengths:
   Gemini CLI automatically applies default lengths for supported field types like `string` or `decimal`.

---

### **3. make:entity with QueryBuilder**

Generates an **Entity Class** that extends a `QueryBuilder` for database interaction.

**Syntax:**

```bash
php gemini make:entity <EntityName> --entity-qb --table=<TableName> [--fields="<field:type,field:type>"]
```

**Examples:**

```bash
php gemini make:entity User --entity-qb --table=users --fields="id:int,name:string,email:string"
```

This will generate:

- A **User Entity** class extending the QueryBuilder.
- The Entity includes getters and setters for each field.

---

### **4. make:repository**

Generates a **Repository Class** to handle database operations for an entity.

**Syntax:**

```bash
php gemini make:repository <RepositoryName> --entity=<EntityName>
```

**Examples:**

```bash
php gemini make:repository UserRepository --entity=User
```

The generated repository includes:

- Methods like `find(int $id)`, `save(Entity $entity)`, and `delete(int $id)`.

---

### **5. make:playerDto**

Generates a **Data Transfer Object (DTO)** class.

**Syntax:**

```bash
php gemini make:playerDto <DtoName> --fields="<field:type,field:type>"
```

**Examples:**

```bash
php gemini make:playerDto UserDto --fields="id:int,name:string,email:string"
```

---

### **6. make:service**

Generates a **Service Class** to encapsulate business logic.

**Syntax:**

```bash
php gemini make:service <ServiceName>
```

**Examples:**

```bash
php gemini make:service UserService
```

---

### **7. make:controller**

Generates a **Controller Class** for RESTful operations.

**Syntax:**

```bash
php gemini make:controller <ControllerName>
```

**Examples:**

```bash
php gemini make:controller UserController
```

The generated controller includes:

- Methods like `index`, `show`, `store`, `update`, and `destroy` with placeholders for implementation.

---

### **8. validate:arguments**

Validates the existence and readability of stub files.

**Syntax:**

```bash
php gemini validate:arguments --arguments="stub1.stub,stub2.stub"
```

**Examples:**

```bash
php gemini validate:arguments --arguments="controller.stub,entity.stub"
```

---

## **Combining Features**

Gemini CLI allows you to **combine multiple features** in a single command. This is especially useful for scaffolding an
entire component set at once.

### **Example: Generate Migration + Entity + Repository + DTO**

```bash
php gemini make:migration CreateUsersTable --table=users --fields="id:int,name:string,email:string" --entity --playerDto --repository
```

This generates:

1. A **Migration File** for the `users` table.
2. An **Entity Class** for `User`.
3. A **Repository Class** for managing `User` entities.
4. A **DTO Class** for transferring `User` data.

---

### **Example: Generate Entity with QueryBuilder + Repository**

```bash
php gemini make:entity User --entity-qb --table=users --repository --fields="id:int,name:string,email:string"
```

This generates:

1. An **Entity Class** for `User` that extends the `QueryBuilder`.
2. A **Repository Class** for managing `User` entities.

---

### **Example: Generate Entity + Service**

```bash
php gemini make:entity Product --fields="id:int,name:string,price:decimal" --entity --service
```

This generates:

1. An **Entity Class** for `Product`.
2. A **Service Class** for handling business logic for `Product`.

---

### **Example: Generate Controller**

```bash
php gemini make:controller UserController
```

This generates:

1. A **Controller Class** for `User` with RESTful methods.

---

## **Advanced Configuration**

Gemini CLI uses a configuration file (`app.php`) for global settings. Here’s an example configuration:

```
<?php

declare(strict_types=1);

return [
    'namespaces' => [
        'DTO'          => 'App\Domain\DTO',
        'Entity'       => 'App\Domain\Entities',
        'Migrations'   => 'App\Infrastructure\Migrations',
        'Repositories' => 'App\Infrastructure\Repositories',
        'Services'     => 'App\Infrastructure\Services',
        'Controllers'  => 'App\Presentation\HTTP\Controllers',
    ],
    'paths'      => [
        'DTO'          => base_path(path: 'App/Domain/DTO'),
        'Entity'       => base_path(path: 'App/Domain/Entities'),
        'Migrations'   => base_path(path: 'App/Infrastructure/Migrations'),
        'Repositories' => base_path(path: 'App/Infrastructure/Repositories'),
        'Services'     => base_path(path: 'App/Infrastructure/Services'),
        'Controllers'  => base_path(path: 'App/Presentation/HTTP/Controllers'),
    ],
];

```

### **Using Default Stub Path**

If no `stub_path` is provided, Gemini CLI falls back to its default stub directory.

---

## **FAQ**

### **1. Can I use Gemini CLI without arguments?**

No, Gemini CLI relies on stub files to generate code. Use `validate:arguments` to ensure all required arguments exist.

### **2. How do I update the default field lengths?**

You can modify the `getDefaultLength` method in `MakeMigrationCommand` for your preferred defaults.

---

## **Final Notes**

Gemini CLI is a powerful tool to streamline application development. With the ability to generate components
individually or as a set, it reduces boilerplate code and accelerates the development process. Experiment with the
various commands and combinations to unlock its full potential!

Happy coding! 🚀