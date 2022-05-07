<?php

namespace LuisaeDev\ArrayTools;

/**
 * Few but useful methods for handle associative arrays.
 */
class ArrayTools
{

    /**
     * Check if an array is associative or sequential.
     *
     * @param array $value Value to check
     * 
     * @return bool
     */
    public static function isAssociative(array $value): bool
    {
        if (is_array($value)) {
            return array_keys($value) !== range(0, count($value) - 1);
        } else {
            return false;
        }
    }

    /**
     * Walk each node from an associative array and alter the array passed.
     * 
     *
     * Calls a clousure at each iteration and pass the current associative node.
     * The returned value from the clousure, will substitute the current node
     * If the clousure returns a null value, the current node and its nested nodes will be excluded from the original array.
     *
     * @param array    $value Array to walk
     * @param callable $cb    Clousure to call at each iteration
     * @param array    $path  Reserved argument, set the nested path corresponding to the current node
     *
     * @return array Array altered
     */
    public static function walkAssociative(array $value, callable $cb, array $path = array()): array
    {
        foreach ($value as $key => $node) {

            // Set the current path
            array_push($path, $key);

            if (is_array($node)) {
                if (self::isAssociative($node)) {

                    // Calls the clousure and pass the current node
                    $node = call_user_func($cb, $node, $path);
                    
                    // If the result is an array value, the node will be walked as a recursive call
                    if (is_array($node)) {
                        $value[$key] = self::walkAssociative($node, $cb, $path);
                    
                    // If the result is a null value, the node will be exclude
                    } else if ($node === null) {
                        unset($value[$key]);
                    
                    // If the result is something else, the result will substitute the current node
                    } else {
                        $value[$key] = $node;
                    }

                } else {
                    $value[$key] = self::walkAssociative($node, $cb, $path);
                }
            }

            // Removes the last path previous to the next iteration
            array_splice($path, -1);
        }
        return $value;
    }

    /**
     * Walk an array through a certain path.
     * 
     * @param array    $value Array to walk
     * @param array    $path  Path to follow, each position in this array represents an index corresponding as a sequential way for walk the array
     * @param callable $cb    Clousure to call at each iteration
     * @param int      $step  Reserved argument, set the current step corresponding to the nested path
     * 
     * @return array Array altered
     */
    private function walkPath(array $value, array $path, callable $cb, int $step = 0): array
    {

        // Selects the node corresponding to the current step
        $node = $value[$path[$step]];

        // Calls a clousure at each iteration and pass the current associative node.
        $node = call_user_func($cb, $node);

        // If it isn't the last step
        if ($step < count($path) - 1) {
            $next = $step + 1;
            $node = self::walkPath($node, $path, $cb, $next);
        }

        // Update the altered node at the array
        $value[$path[$step]] = $node;

        return $value;
    }
}
?>