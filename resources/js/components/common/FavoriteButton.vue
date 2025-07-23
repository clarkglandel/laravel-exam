<template>
    <div>
        <button
            class="favorite-button"
            :class="{
                'favorite-button--favorited': isFavorited,
                'favorite-button--loading': loading,
                'favorite-button--small': size === 'small',
                'favorite-button--large': size === 'large',
            }"
            @click="handleToggle"
            :disabled="loading"
            :title="isFavorited ? 'Remove from favorites' : 'Add to favorites'"
        >
            <div v-if="loading" class="favorite-button__spinner"></div>
            <template v-else>
                <!-- Filled heart when favorited -->
                <svg
                    v-if="isFavorited"
                    class="favorite-button__icon"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                >
                    <path
                        d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"
                    />
                </svg>
                <!-- Empty heart when not favorited -->
                <svg
                    v-else
                    class="favorite-button__icon"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"
                    />
                </svg>
                <span v-if="showText" class="favorite-button__text">
                    {{
                        isFavorited
                            ? "Remove from Favorites"
                            : "Add to Favorites"
                    }}
                </span>
            </template>
        </button>
    </div>
</template>

<script>
import { ref, onMounted, watch } from "vue";
import { checkFavoriteStatus, toggleFavorite } from "@/api/movie-api";

export default {
    name: "FavoriteButton",
    props: {
        movie: {
            type: Object,
            required: true,
        },
        size: {
            type: String,
            default: "medium", // small, medium, large
            validator: (value) => ["small", "medium", "large"].includes(value),
        },
        showText: {
            type: Boolean,
            default: false,
        },
    },
    emits: ["favoriteChanged"],
    setup(props, { emit }) {
        const isFavorited = ref(false);
        const loading = ref(false);

        const getImdbId = () => {
            return props.movie.imdb_id || props.movie.imdbID || props.movie.id;
        };

        const checkStatus = async () => {
            const imdbId = getImdbId();
            if (!imdbId) return;

            try {
                const response = await checkFavoriteStatus(imdbId);
                isFavorited.value = response.is_favorited;
            } catch (error) {
                console.error("Failed to check favorite status:", error);
            }
        };

        const handleToggle = async () => {
            if (loading.value) return;

            loading.value = true;

            try {
                const response = await toggleFavorite(props.movie);
                isFavorited.value = response.is_favorited;

                // Emit event for parent components to react
                emit("favoriteChanged", {
                    movie: props.movie,
                    isFavorited: response.is_favorited,
                    action: response.is_favorited ? "added" : "removed",
                });
            } catch (error) {
                console.error("Failed to toggle favorite:", error);
                // You could emit an error event here if needed
            } finally {
                loading.value = false;
            }
        };

        // Check status when component mounts
        onMounted(() => {
            checkStatus();
        });

        // Re-check status when movie changes
        watch(
            () => props.movie,
            () => {
                checkStatus();
            },
            { deep: true }
        );

        return {
            isFavorited,
            loading,
            handleToggle,
        };
    },
};
</script>

<style lang="scss" scoped>
.favorite-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: transparent;
    border: 2px solid #6b7280;
    border-radius: 8px;
    color: #6b7280;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;

    &:hover:not(:disabled) {
        border-color: #ef4444;
        color: #ef4444;
        background: rgba(239, 68, 68, 0.05);
    }

    &:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    &--favorited {
        border-color: #ef4444;
        color: #ef4444;
        background: rgba(239, 68, 68, 0.1);

        &:hover:not(:disabled) {
            background: rgba(239, 68, 68, 0.15);
        }
    }

    &--loading {
        color: #6b7280;
        border-color: #6b7280;
    }

    &--small {
        padding: 0.5rem;
        border-radius: 6px;

        .favorite-button__icon {
            width: 16px;
            height: 16px;
        }

        .favorite-button__text {
            font-size: 0.875rem;
        }
    }

    &--large {
        padding: 1rem 1.5rem;
        border-radius: 12px;

        .favorite-button__icon {
            width: 24px;
            height: 24px;
        }

        .favorite-button__text {
            font-size: 1.1rem;
        }
    }
}

.favorite-button__icon {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
    transition: transform 0.2s ease;
}

.favorite-button:hover:not(:disabled) .favorite-button__icon {
    transform: scale(1.1);
}

.favorite-button__text {
    font-size: 1rem;
    white-space: nowrap;
}

.favorite-button__spinner {
    width: 20px;
    height: 20px;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

// Dark theme adjustments
:root.dark-theme .favorite-button {
    border-color: #9ca3af;
    color: #9ca3af;

    &:hover:not(:disabled) {
        border-color: #ef4444;
        color: #ef4444;
        background: rgba(239, 68, 68, 0.1);
    }

    &--favorited {
        border-color: #ef4444;
        color: #ef4444;
        background: rgba(239, 68, 68, 0.15);

        &:hover:not(:disabled) {
            background: rgba(239, 68, 68, 0.2);
        }
    }
}
</style>
