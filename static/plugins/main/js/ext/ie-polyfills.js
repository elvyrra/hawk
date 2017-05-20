'use strict';

/**
 * Array.from polyfill
 */
if(!Array.from) {
    Array.from = (function () {
        var toStr = Object.prototype.toString;
        var isCallable = function (fn) {
            return typeof fn === 'function' || toStr.call(fn) === '[object Function]';
        };
        var toInteger = function (value) {
            var number = Number(value);
            if (isNaN(number)) {
                return 0;
            }
            if (number === 0 || !isFinite(number)) {
                return number;
            }
            return (number > 0 ? 1 : -1) * Math.floor(Math.abs(number));
        };

        var maxSafeInteger = Math.pow(2, 53) - 1;
        var toLength = function (value) {
            var len = toInteger(value);
            return Math.min(Math.max(len, 0), maxSafeInteger);
        };

        // La propriété length de la méthode vaut 1.
        return function from(arrayLike/*, mapFn, thisArg */) {
            // 1. Soit C, la valeur this
            var C = this;

            // 2. Soit items le ToObject(arrayLike).
            var items = Object(arrayLike);

            // 3. ReturnIfAbrupt(items).
            if (arrayLike == null) {
                throw new TypeError("Array.from doit utiliser un objet semblable à un tableau - null ou undefined ne peuvent pas être utilisés");
            }

            // 4. Si mapfn est undefined, le mapping sera false.
            var mapFn = arguments.length > 1 ? arguments[1] : void undefined;
            var T;
            if (typeof mapFn !== 'undefined') {
                // 5. sinon
                // 5. a. si IsCallable(mapfn) est false, on lève une TypeError.
                if (!isCallable(mapFn)) {
                    throw new TypeError('Array.from: lorsqu il est utilisé le deuxième argument doit être une fonction');
                }

                // 5. b. si thisArg a été fourni, T sera thisArg ; sinon T sera undefined.
                if (arguments.length > 2) {
                    T = arguments[2];
                }
            }

            // 10. Soit lenValue pour Get(items, "length").
            // 11. Soit len pour ToLength(lenValue).
            var len = toLength(items.length);

            // 13. Si IsConstructor(C) vaut true, alors
            // 13. a. Soit A le résultat de l'appel à la méthode interne [[Construct]] avec une liste en argument qui contient l'élément len.
            // 14. a. Sinon, soit A le résultat de ArrayCreate(len).
            var A = isCallable(C) ? Object(new C(len)) : new Array(len);

            // 16. Soit k égal à 0.
            var k = 0;  // 17. On répète tant que k < len…
            var kValue;
            while (k < len) {
                kValue = items[k];
                if (mapFn) {
                    A[k] = typeof T === 'undefined' ? mapFn(kValue, k) : mapFn.call(T, kValue, k);
                } else {
                    A[k] = kValue;
                }
                k += 1;
            }
            // 18. Soit putStatus égal à Put(A, "length", len, true).
            A.length = len;  // 20. On renvoie A.

            return A;
        };
    }());
}

/**
 * Set polyfll
 */
if(!window.Set) {
  /**
     * This class defines the ES6 Set object, to be compatible with non ES6 supporting browsers
     * (IE sucks!!)
     */
    var Set = function(iterable) {
        var arr = Array.from(iterable);
        this.content = [];

        arr.forEach(function(item) {
            this.add(item);
        }.bind(this));
    };

    window.Set = Set;

    /**
     * Add a value to the set
     * @param {mixed} value The value to add
     * @returns {Set} The Set itself
     */
    Set.prototype.add = function(value) {
        if(this.content.indexOf(value) === -1) {
            this.content.push(item);
        }

        return this;
    };

    /**
     * remove all the Set values
     * @return {undefined} undefined
     */
    Set.prototype.clear = function() {
        this.content = [];
    }

    /**
     * Delete a value from the Set
     * @param  {miwed} value The value to delete
     * @return {boolean}     True if the value has been removed, else false
     */
    Set.prototype.delete = function(value) {
        var index = this.content.indexOf(value);

        if(index !== -1) {
            this.content.splice(index, 1);

            return true;
        }

        return false;
    }

    /**
     * Execute the function callback for each element in the Set
     * @param  {Function} callback The callback to execute
     * @return {undefined}         undefined
     */
    Set.prototype.forEach = function(callback) {
        this.content.forEach(function(item) {
            callback(item, item, this);
        }.bind(this));
    }

    /**
     * Check if a value is in the Set
     * @param  {mixed}  value The value to search
     * @return {Boolean}      True if the value is present in set, else False
     */
    Set.prototype.has = function(value) {
        return this.content.indexOf(value) !== -1;
    }


    Set.prototype.entries = function() {
        return this.content.entries();
    }

    Set.prototype.values = function() {
        return this.content.values();
    }

    Set.prototype.keys = function() {
        return this.values();
    }
}

if(!Array.prototype.findIndex) {
    Array.prototype.findIndex = function(callback) {
        for(var i = 0; i< this.length; i++) {
            if(callback(this[i], i)) {
                return i;
            }
        }

        return -1;
    };
}

if(!Array.prototype.find) {
    Array.prototype.find = function(callback) {
        for(var i = 0; i < this.length; i++) {
            if(callback(this[i], i)) {
                return this[i];
            }
        }

        return null;
    };
}