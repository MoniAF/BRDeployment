<script>
import { useFiltersStore } from '../stores/filters'
import { Modal } from 'bootstrap'
import { useLikesStore } from '../stores/likes'

export default {
    setup() {
        const filtersStore = useFiltersStore()
        const likesStore = useLikesStore()

        return {
            filtersStore,
            likesStore
        }
    },

    mounted() {
        this.filtersStore.loadFilters()
    },

    emits: ['openrecipes', 'openhome', 'searchrecipes', 'showdetails', 'recipelike', 'recipeunlike', 'showlevels', 'showoccasions', 'showcategories', 'logout'],
    
    props:{
        savedrecipes: {
            type: Array,
            default: () => [],
        },
        trendings: {
            type: Array,
            default: () => [],
        },
        online:{
            type: Boolean,
            default: false
        },
        username:{
            type: String,
            default: "default username"
        },
        name:{
            type: String,
            default: "default name"
        },
        email:{
            type: String,
            default: "default email"
        }
    },
    
    data() {
        return {
            searchTerm:""
        }
    },
    
    computed: {
        menuSections() {
            const makeColumns = (filter, includeAll = false) => {
                const group = this.filtersStore.groups.find(
                    (item) => item.filter === filter
                )

                if (!group) return []

                const options = group.options.map((option) => ({
                    ...option,
                    label: filter === 'level'
                        ? option.label.replace(/\s+recipes$/i, '')
                        : option.label,
                }))

                if (includeAll) {
                    options.unshift({
                        id: 'all',
                        label: 'All',
                    })
                }

                const columns = []

                for (let index = 0; index < options.length; index += 3) {
                    columns.push({
                        key: `${filter}-${index}`,
                        filter,
                        event: group.event,
                        title: index === 0 ? group.title : '',
                        options: options.slice(index, index + 3),
                    })
                }

                return columns
            }

            return [
                {
                    key: 'difficulty-category',
                    columns: [
                        ...makeColumns('level'),
                        ...makeColumns('category'),
                    ],
                },
                {
                    key: 'occasion',
                    columns: makeColumns('occasion', true),
                },
            ]
        },
    },
    
    methods: {
        showRecipes(){
            this.$emit('openrecipes', this);
        },
        showHome(){
            this.$emit('openhome', this);
        },
        onClickSearchRecipe() {
            const term = this.searchTerm.trim()

            if (!term) return

            this.$emit('searchrecipes', term)
            this.searchTerm = ''
        },
        goToAuthPage(routeName) {
        const element = document.getElementById('messageModal')

        if (!element || !element.classList.contains('show')) {
            this.$router.push({ name: routeName })
            return
        }

        element.addEventListener(
            'hidden.bs.modal',
            () => {
            this.$router.push({ name: routeName })
            },
            { once: true },
        )

        Modal.getOrCreateInstance(element).hide()
        },
        onClickShowDetails(id){
            this.$emit('showdetails', id);
        },
        onClickRecipeLike(id){
            this.$emit('recipelike', id);
        },
        onClickRecipeUnlike(id){
            this.$emit('recipeunlike', id);
        },
        showLevels(id){
            this.$emit('showlevels', id);
        },
        showOccasions(id){
            this.$emit('showoccasions', id);
        },
        showCategories(id){
            this.$emit('showcategories', id);
        },
        onClickLogout() {
            this.$emit('logout', this);
        }
    }
}
</script>

<template>
    <!-- Modales -->
          <div class="modal fade" id="trendingModal" tabindex="-1" role="dialog" aria-labelledby="trendingModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered custom-modal modal-dialog-scrollable">
              <div class="modal-content modal-design">

                <div class="modal-header header-mc">
                  <h5 class="modal-title" id="trendingModalLabel">Trending recipes</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="d-fb cards-modal justify-content-between">

                        <div class="size-card" v-for="element in trendings" :key="element.id">
                            <div class="conf-cards">
                                <div class="card-top">

                                    <SaveRecipeButton :recipe-id="element.id" class="card-save-button"/>

                                    <LikeRecipeButton :recipe-id="element.id" :likes="element.likes" :show-count="false" class="card-like-button"/>

                                    <button v-if="online" v-show="element.onLike" v-on:click.stop="onClickRecipeLike(element.id)" class="btn-heart"><svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-heart-fill like-icon" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314z"/></svg></button>

                                    <button v-if="online" v-show="element.onUnlike" v-on:click.stop="onClickRecipeUnlike(element.id)" class="btn-heart"><svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-heart-fill unlike-icon" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314z"/></svg></button>

                                    <section class="img-csz">
                                        <img v-bind:src="element.image" class="img-card" :alt="element.name">
                                    </section>
                                    
                                    <div class="degraded"></div>

                                    <section class="d-flex justify-content-center">
                                        <div class="info-top">

                                            <p class="title-card text-center">
                                            <button type="button" class="recipe-title-button" data-bs-dismiss="modal" @click="onClickShowDetails(element.id)"> {{ element.name }} </button>
                                            </p>

                                            <p class="category-card text-center categories-txt">{{ element.category }}</p>

                                            <p class="category-card text-center categories-txt">{{ element.difficulty?.replace(/\s+recipes$/i, '') }}</p>

                                            <p class="txt-likes text-center"><span><svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-heart-fill card-heart" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314z"/></svg></span> {{ likesStore.countFor(element.id, element.likes) }} </p>
                                            
                                        </div>
                                    </section>    
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                  <button type="button" class="btn-cmodal" data-bs-dismiss="modal">Close</button>
                </div>

              </div>
            </div>
          </div>

          <div class="modal fade" id="aboutUsModal" tabindex="-1" role="dialog" aria-labelledby="aboutUsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
              <div class="modal-content modal-design">

                <div class="modal-header header-mc">
                  <h5 class="modal-title" id="aboutUsModalLabel">About Us</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <section class="text-center">
                        <p class="bm-icon" href="#">BLOOM<br>
                        <span class="rm-icon">RECIPES</span></p>
                    </section>

                    <section class="txt-about">
                        <p>Welcome to Bloom Recipes, a website dedicated to creative and healthy cooking. Our team is made up of passionate cooks who believe in the importance of balanced and nutritious eating. At Bloom Recipes, we strive to create tasty and healthy recipes that cater to the needs of each individual, whether they are looking for vegetarian, vegan or gluten-free options. We love experimenting with different ingredients and flavors to bring a unique and delicious gastronomic experience to your home. We draw inspiration from the beauty of nature and use fresh, high-quality ingredients for our recipes. We work with local farmers and suppliers whenever possible, supporting our communities and ensuring that we offer the highest quality ingredients.<br>
                        At Bloom Recipes, we believe that food should not only be delicious but also nutritious. That's why we strive to create recipes that are rich in vitamins, minerals, and other essential nutrients. We are always learning and exploring new ways to improve our cooking and offer the best possible experience to our users. We appreciate your trust in us and hope you enjoy our recipes as much as we enjoy creating them. Welcome to the Bloom Recipes community!<br>
                        By registering on Bloom Recipes, you can unlock a whole new world of culinary possibilities. Not only will you be able to save your favorite recipes, but you'll also be able to vote for your favorite ones. Your votes help us determine which recipes our community loves the most, and we use this information to curate our recipe selection to best suit your needs and preferences. When you register, you'll also gain access to your liked recipe list. This list is a great way to keep track of the recipes you've enjoyed and would like to try again. It's an easy way to access your favorite recipes quickly, so you can whip up a delicious meal in no time.</p>
                    </section>

                </div>

                <div class="modal-footer">
                  <button type="button" class="btn-cmodal mb-cabout" data-bs-dismiss="modal">Close</button>
                </div>

              </div>
            </div>
          </div>

          <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered custom-msg">
              <div class="modal-content border-msg">
                <div class="modal-body">

                    <div class="d-flex justify-content-center align-items-center mt-3 mb-0">
                        <p class="txt-message text-center">This section is exclusive to<br>registered members.</p>
                    </div>

                    <div class="d-flex justify-content-center align-items-center mb-0">
                          <p class="txt-msgxs text-center">Save your favorite recipes and vote for the ones you love.<br><a href="/register" class="txt-cian" @click.prevent="goToAuthPage('register')"> Sign up </a> or <a href="/login" class="txt-red" @click.prevent="goToAuthPage('login')"> log in </a> to get started. </p>
                    </div>
                    
                </div>
                <div class="modal-footer mb-3">

                    <button type="button" class="btn-cmodal mb-cabout" data-bs-dismiss="modal">Ok</button>

                </div>
              </div>
            </div>
          </div>
    <!-- Modales -->

    <!-- Navbar -->
        <nav class="navbar navbar-h">
            <div class="container-navbar">
    
                <section class="text-center pos-icon">
                    <RouterLink  :to="{ name: 'home' }" class="b-icon">BLOOM<br>
                    <span class="r-icon">RECIPES</span></RouterLink>
                </section>
                
                <div class="d-flex align-items-center m-res">
                    <div class="section-se ms-auto me-auto"></div>

                    <section class="m-options">

                        <button class="btn-nav" v-on:click="showHome">Home</button>

                        <div class="dropdown-center">
                            <button class="btn-menu" type="button" data-bs-toggle="dropdown" aria-expanded="false">Menu</button>
                            <ul class="dropdown-menu custom-menu">
                                <li v-if="filtersStore.loading" class="px-3 py-2" role="status">Loading filters...</li>

                                <li v-else-if="filtersStore.error" class="px-3 py-2" role="alert">

                                    <p>{{ filtersStore.error }}</p>

                                    <button type="button" class="btn btn-outline-dark" @click="filtersStore.loadFilters()">Try again</button>

                                </li>

                                <li v-else class="menu-options d-fbd menu-gap">

                                    <section v-for="section in menuSections" :key="section.key" class="menu-options d-fb menu-gap">

                                        <div v-for="column in section.columns" :key="column.key" class="d-block">
                                            <p class="title-categories" :class="{ 'h-transparent': !column.title }"> {{ column.title }} </p>

                                            <ul class="list-unstyled m-0">
                                                <li v-for="option in column.options" :key="option.id">
                                                    <a href="#" :class="[ column.filter === 'category' ? 'btn-category' : 'btn-do', 'text-center', ]" @click.prevent=" option.id === 'all' ? showRecipes() : $emit(column.event, option.id)"> {{ option.label }} </a>
                                                </li>
                                            </ul>
                                        </div>

                                    </section>
                                </li>

                            </ul>
                        </div>

                        <button type="button" class="btn-nav" data-bs-toggle="modal" data-bs-target="#imagesModal">Gallery</button>

                        <button v-if="online" type="button" class="btn-nav" data-bs-toggle="modal" data-bs-target="#favoritesModal">Favorites</button>
                        <button v-else type="button" class="btn-nav" data-bs-toggle="modal" data-bs-target="#messageModal">Favorites</button>

                        <button type="button" class="btn-nav" data-bs-toggle="modal" data-bs-target="#aboutUsModal">About Us</button>

                    </section>

                    <div class="section-se ms-auto me-auto"></div>
                
                    <form class="d-flex m-form" role="search" @submit.prevent="onClickSearchRecipe">
                        <input v-model="searchTerm" class="search-input mt-auto pt-0 pb-0" type="text" placeholder="Search" aria-label="Search recipes">

                        <button class="btn btn-outline-success btn-search mt-auto pt-0 pb-0" type="submit"> Search </button>
                    </form>
    
                    <section class="user-r d-flex">

                        <div v-if="online" class="dropdown-center">
                            <button class="btn-user" type="button" data-bs-toggle="dropdown" aria-expanded="false"> {{username}} </button>

                            <ul class="dropdown-menu custom-user">

                                <p class="title-iuser">E-mail:</p>
                                <p class="txt-iuser"> {{email}} </p>

                                <p class="title-iuser">Full name:</p>
                                <p class="txt-iuser"> {{name}} </p>

                                <section class="d-flex btn-sout">
                                    <button v-on:click="onClickLogout" class="btn-out d-flex">Log out <span class="material-symbols-outlined"> logout </span></button>
                                </section>

                            </ul>
                        </div>
                        <section v-else>
                            <RouterLink :to="{ name: 'login' }" class="btn-user d-block text-decoration-none">Log In</RouterLink>
                        </section>

                        <img src="/img/icon-admin.jpg" class="user-icon" alt="User">

                    </section>
                </div>
                
            </div>
        </nav>
    <!-- Navbar -->
</template>