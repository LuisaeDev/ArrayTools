<?php

namespace LuisaeDev\ArrayTools;

/**
 * Useful collection of to handle complex array values.
 */
class ArrayTools
{

    /**
     * Checks if an array is associative or sequential.
     *
     * @param array $value Value to check
     * 
     * @return bool
     */
    public static function isAssociative(array $value)
    {
        if (is_array($value)) {
            return array_keys($value) !== range(0, count($value) - 1);
        } else {
            return false;
        }
    }
    
	/**
	 * Transforms an entire object to an array.
	 *
	 * @param array $object Object to transform
	 *
	 * @return array
	 */
	public static function objectToArray(array $object)
    {
		if (!is_object($object) && !is_array($object)) {
			return $object;
		} else {
			return array_map('self::objectToArray', (array) $object);
		}
	}

    /**
     * Walks each node from an associative array and alters the array passed.
     * 
     *
     * Calls a clousure at each iteration and pass the current associative node.
     * The returned value from the clousure, will substitute the current node
     * If the clousure returns a null value, the current node and its nested nodes will be excluded from the original array.
     *
     * @param array    $array Array to walk
     * @param callable $cb    Clousure to call at each iteration
     * @param array    $path  Reserved argument, set the nested path corresponding to the current node
     *
     * @return array Array altered
     */
    public static function walkAssociative(array $array, callable $cb, array $path = array())
    {
        foreach ($array as $key => $node) {

            // Set the current path
            array_push($path, $key);

            if (is_array($node)) {
                if (self::isAssociative($node)) {

                    // Calls the clousure and pass the current node
                    $node = call_user_func($cb, $node, $path);
                    
                    // If the result is an array value, the node will be walked as a recursive call
                    if (is_array($node)) {
                        $array[$key] = self::walkAssociative($node, $cb, $path);
                    
                    // If the result is a null value, the node will be exclude
                    } else if ($node === null) {
                        unset($array[$key]);
                    
                    // If the result is something else, the result will substitute the current node
                    } else {
                        $array[$key] = $node;
                    }

                } else {
                    $array[$key] = self::walkAssociative($node, $cb, $path);
                }
            }

            // Removes the last path previous to the next iteration
            array_splice($path, -1);
        }
        return $array;
    }

    /**
     * Walks an array through a certain path.
     * 
     * @param array    $array Array to walk
     * @param array    $path  Path to follow, each position in this array represents an index corresponding as a sequential way for walk the array
     * @param callable $cb    Clousure to call at each iteration
     * @param int      $step  Reserved argument, set the current step corresponding to the nested path
     * 
     * @return array Array altered
     */
    private function walkPath(array $array, array $path, callable $cb, int $step = 0)
    {

        // Selects the node corresponding to the current step
        $node = $array[$path[$step]];

        // Calls a clousure at each iteration and pass the current associative node.
        $node = call_user_func($cb, $node);

        // If it isn't the last step
        if ($step < count($path) - 1) {
            $next = $step + 1;
            $node = self::walkPath($node, $path, $cb, $next);
        }

        // Update the altered node at the array
        $array[$path[$step]] = $node;

        return $array;
    }
}
?>