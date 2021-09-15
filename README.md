# Kick-In/Hungarian
_PHP implementation of various algorithms for the Linear Assignment Problem._

![Build status](https://travis-ci.org/Kick-In/hungarian.svg?branch=master)

## Documentation
The original paper by Jonker and Volgenant can be found [SpringerLink](https://link.springer.com/article/10.1007%2FBF02278710) or in the [```doc/``` folder](https://github.com/Kick-In/hungarian/blob/master/doc/).

## Usage
The hungarian library contains the fundamentals needed to solve a linear assignment problem, as well as some abstractions to make integration easier.

### Basic usage
The plain matrix object is indexed by integer and allows getting and setting of values, it is always a square matrix.
To fill a three by three matrix, you might do the following.
```php
use Kickin\Hungarian\Matrix\Matrix;

$matrix = new Matrix(3);
$matrix->set(0, 0, 10);
$matrix->set(1, 0, 15);
$matrix->set(2, 0, 12);
$matrix->set(0, 1, 12);
$matrix->set(1, 1, 13);
$matrix->set(2, 1, 14);
$matrix->set(0, 2, 15);
$matrix->set(1, 2, 17);
$matrix->set(2, 2, 25);
```

Using the matrix above, we can use the Hungarian method.
```php
use Kickin\Hungarian\Algo\Hungarian;

$solver = new Hungarian();
$result = $solver->solve($matrix);
```

Or, if you'd want to find the highest scoring assignment, you can call use `solveMax()`.
```php
$result = $solver->solveMax($matrix);
```
Under the hood, this is equivalent to solving the matrix after calling `invert()`.

This result can then be used as a list of tuples.
```php
foreach($result as [$row, $col]){
  echo $row . ": " . $col . "\n";
}
```

### Alternate types of matrices
In most cases, you're not actually trying to pair integers, instead you might want to assign users to tasks. One way to do this, is by using string labels

```php
use Kickin\Hungarian\Matrix\StringMatrix;

$matrix = new StringMatrix(["Alice", "Bob", "Carol"], ["Bathroom", "Kitchen", "Windows"]);
$matrix->set("Alice", "Bathroom", 10);
$matrix->set("Bob",   "Bathroom", 15);
$matrix->set("Carol", "Bathroom", 12);
$matrix->set("Alice", "Kitchen",  12);
$matrix->set("Bob",   "Kitchen",  13);
$matrix->set("Carol", "Kitchen",  14);
$matrix->set("Alice", "Windows",  15);
$matrix->set("Bob",   "Windows",  17);
$matrix->set("Carol", "Windows",  25);
```

Another option is by using objects as labels, for example using Eloquent

```php
use Kickin\Hungarian\Matrix\LabeledMatrix;

$matrix = new LabeledMatrix(User::all(), Task::all());
```

### MatrixBuilder
Finally, it is likely you don't have a square matrix or need to write quite some boilerplate code to create a proper matrix. To help in this, you can use the `MatrixBuilder` class.
This will automatically ensure your matrix is square, augmenting it where needed.

```php
use Kickin\Hungarian\Matrix\MatrixBuilder;

$builder = new MatrixBuilder();
$builder->setRowSource(["Alice", "Bob", "Carol", "Dave"]);
$builder->setColSource(["Garbage", "Sweep floor"]);
$builder->setDefaultValue(1);
$builder->setAugmentValue(10);
$builder->setMappingFunction(function($row, $col){
  return 1; // Define your own scoring for any assignment pair
});

$matrix = $builder->build();
```

If desired, you can easily remove unassigned rows and columns from the results

```php
$result = $solver->solve($matrix);
$assignedOnly = $result->withoutUnassigned();
```
