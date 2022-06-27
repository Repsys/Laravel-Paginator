# Laravel-Paginator
Pretty paginator facade for Laravel

## Install
1. Add repository to composer.json:
```json
"repositories": [
  {
      "type": "vcs",
      "url": "https://gitlab.smartworld.team/arhipov_leonid/paginator"
  },
  ...
],
...
```
2. Install package via composer:
```
composer require leonidark/paginator
```

## Usage
```php
use Paginator;
//...

public function getUserTasks(User $user, $search = null): array
{
    $query = $user->tasks();

    if (!is_null($search)) {
        $query = $this->queryAddSearch($query, $search);
    }

    // Get paginated query
    $query = Paginator::paginate($query);

    // Get collection of paginated tasks
    $data = $query->get();

    // Assign additional params, such as limit, page, offset, maxPage, count then return an array
    return Paginator::assignParams($data);
}

```
