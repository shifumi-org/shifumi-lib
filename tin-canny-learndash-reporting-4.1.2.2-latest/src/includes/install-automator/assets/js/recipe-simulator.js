jQuery(function ($) {
    $(document).ready(function () {
        RecipeSimulator.init();
    });

    var RecipeSimulator = {
        init: function () {
            // Check if we have to invoke the methods
            if (this.hasRecipeSimulator()) {
                // Get all simulators
                let $simulators = $('.uo-recipe-simulator');

                // Init each simulator
                let _this = this,
                    Simulator;

                $simulators.each(function (index) {
                    Simulator = $.extend(true, {}, _this.Simulator);
                    Simulator.init($(this));
                });
            }
        },

        hasRecipeSimulator: function () {
            let output = false;

            if (typeof window.hasRecipeSimulator !== 'undefined' || window.hasRecipeSimulator !== null && window.hasRecipeSimulator == true) {
                output = true;
            }

            return output;
        },

        Simulator: {
            config: {
                visibleItems: 8, // How many items do you want to show?
                maxSelected: 3, // How many tasks do you want to select? this number is the maximum, 1 is the minimum
                carousel: {
                    interval: 5000, // Time between each change in ms
                    duration: 600, // The animation duration
                }
            },

            $elements: {},

            init: function ($simulator, $list, $items) {
                // Save elements
                this.$elements.simulator = $simulator;
                this.$elements.list = $simulator.find('.uo-recipe-simulator__items ul'),
                    this.$elements.items = $simulator.find('.uo-recipe-simulator__items li');

                // Check visible items
                if (this.$elements.items.length < this.config.visibleItems) {
                    this.config.visibleItems = this.$elements.items.length;
                }

                // Shuffle Items
                this.shuffleItems();

                // Set new height based on the visible items
                this.setCarouselHeight();

                // Move one time to init
                this.moveItemCarousel();

                // Create Carousel
                this.playCarousel();
            },

            shuffleItems: function () {
                while (this.$elements.items.length) {
                    this.$elements.list.append(this.$elements.items.splice(Math.floor(Math.random() * this.$elements.items.length), 1)[0]);
                }

                // Get new order
                this.$elements.items = this.$elements.list.find('li');

                // Get first item
                this.$elements.firstItem = this.$elements.items.eq(0);
            },

            playCarousel() {
                // Start carousel animation
                setTimeout(() => {
                    // Move one time
                    this.moveItemCarousel();

                    // Play carousel
                    this.playCarousel();
                }, this.config.carousel.interval);
            },

            moveItemCarousel() {
                // Remove selected elements before moving
                this.$elements.items.removeClass('uo-recipe-simulator__item--selected');

                // Move element
                this.$elements.firstItem.animate({ marginTop: - this.$elements.firstItem.outerHeight(), opacity: 0 }, this.config.carousel.duration, () => {
                    // Move element to the end
                    this.$elements.firstItem.appendTo(this.$elements.list);

                    // Show element
                    this.$elements.firstItem.css({ 'margin': 0 });
                    this.$elements.firstItem.animate({ opacity: 1 }, this.config.carousel.duration);

                    // Find new first element
                    this.$elements.firstItem = this.$elements.list.find('li:first');

                    // Update items
                    this.$elements.items = this.$elements.list.find('li');

                    // Set new height based on the new visible items
                    this.setCarouselHeight();

                    // Select new items
                    this.getRandomItems().addClass('uo-recipe-simulator__item--selected');

                });
            },

            setCarouselHeight() {
                // Set new height based on the new visible items
                this.$elements.list.css({ height: Tools.sumHeight(this.getVisibleItems()) });
            },

            getVisibleItems() {
                // Get all the silibings elements and get the following ( this.config.visibleItems - 1 ). -1 because nextAll doesn't return itself
                return $([this.$elements.firstItem, this.$elements.firstItem.nextAll().slice(0, this.config.visibleItems - 1)]).map(function () { return this.get() });
            },

            getRandomItems() {
                // Get visible items
                let items = this.getVisibleItems(),
                    randomItems = [];

                // Get the number of items to select
                let toSelect = Tools.randomNumber(1, this.config.maxSelected);

                // Get random items
                for (let i = 1; i <= toSelect; i++) {
                    // Select a random item
                    let randomSelect = Tools.randomNumber(0, items.length - 1),
                        randomItem = items.eq(randomSelect);

                    // Save random item
                    randomItems.push(randomItem);

                    // Delete element from items
                    items.filter(function () {
                        let $this = $(this),
                            output = true;

                        if ($this.is(randomItem)) {
                            output = false;
                        }
                    });
                }

                // Return items ( in jQuery wrapped set of elements )
                return $(randomItems).map(function () { return this.get() });
            }
        },
    }

    var Tools = {
        contains_element: function (haystack, arr) {
            /*
            * Check if an array has at least on element from another array
            * Example:
            * A = [1, 2, 3]; B = [5, 3, 7]; C = [8, 9, 10]
            * contains_element( A, B ) => true
            * contains_element( A, C ) => false
            * contains_element( B, C ) => false
            */

            return arr.some(function (v) {
                return haystack.indexOf(v) >= 0;
            });
        },

        sumHeight(nodes) {
            let sum = 0;

            nodes.each(function () {
                sum += $(this).outerHeight();
            });

            return sum;
        },

        randomNumber: function (min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }
    }

});