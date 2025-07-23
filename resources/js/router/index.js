import { createRouter, createWebHistory } from "vue-router";
import MovieSearchPage from "@/components/movie/MovieSearchPage.vue";
import MovieDetails from "@/components/movie/MovieDetails.vue";
import FavoritesPage from "@/components/movie/FavoritesPage.vue";

const routes = [
    {
        path: "/",
        component: MovieSearchPage,
        name: "home",
    },
    {
        path: "/movie/:imdbId",
        component: MovieDetails,
        props: true,
        name: "movie-details",
    },
    {
        path: "/favorites",
        component: FavoritesPage,
        name: "favorites",
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
