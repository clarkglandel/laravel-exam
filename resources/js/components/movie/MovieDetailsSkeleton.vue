<template>
    <div class="movie-details__movie-content">
        <!-- Main Movie Section -->
        <div class="movie-details__main">
            <!-- Movie Poster -->
            <div class="movie-details__poster-container">
                <div
                    class="movie-details__skeleton movie-details__skeleton--poster"
                ></div>
            </div>

            <!-- Movie Info -->
            <div class="movie-details__info">
                <!-- Title -->
                <div
                    class="movie-details__skeleton movie-details__skeleton--title"
                ></div>

                <!-- Meta Information -->
                <div class="movie-details__meta">
                    <div
                        class="movie-details__skeleton movie-details__skeleton--genre"
                    ></div>
                    <div
                        class="movie-details__skeleton movie-details__skeleton--rating"
                    >
                        <div
                            class="movie-details__skeleton movie-details__skeleton--star"
                        ></div>
                        <div
                            class="movie-details__skeleton movie-details__skeleton--rating-text"
                        ></div>
                    </div>
                    <div
                        class="movie-details__skeleton movie-details__skeleton--year"
                    ></div>
                    <div
                        class="movie-details__skeleton movie-details__skeleton--runtime"
                    ></div>
                </div>

                <!-- Plot Summary -->
                <div class="movie-details__plot-section">
                    <div
                        class="movie-details__skeleton movie-details__skeleton--section-title"
                    ></div>
                    <div
                        class="movie-details__skeleton movie-details__skeleton--plot-line"
                    ></div>
                    <div
                        class="movie-details__skeleton movie-details__skeleton--plot-line"
                    ></div>
                    <div
                        class="movie-details__skeleton movie-details__skeleton--plot-line movie-details__skeleton--plot-line-short"
                    ></div>
                </div>

                <!-- Additional Info -->
                <div class="movie-details__additional-info">
                    <div
                        class="movie-details__skeleton movie-details__skeleton--info-item"
                    >
                        <div
                            class="movie-details__skeleton movie-details__skeleton--info-label"
                        ></div>
                        <div
                            class="movie-details__skeleton movie-details__skeleton--info-value"
                        ></div>
                    </div>
                    <div
                        class="movie-details__skeleton movie-details__skeleton--info-item"
                    >
                        <div
                            class="movie-details__skeleton movie-details__skeleton--info-label"
                        ></div>
                        <div
                            class="movie-details__skeleton movie-details__skeleton--info-value movie-details__skeleton--info-value-long"
                        ></div>
                    </div>
                    <div
                        class="movie-details__skeleton movie-details__skeleton--info-item"
                    >
                        <div
                            class="movie-details__skeleton movie-details__skeleton--info-label"
                        ></div>
                        <div
                            class="movie-details__skeleton movie-details__skeleton--info-value"
                        ></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- YouTube Trailer -->
        <div class="movie-details__trailer-section">
            <div
                class="movie-details__skeleton movie-details__skeleton--section-title"
            ></div>
            <div class="movie-details__trailer-container">
                <div
                    class="movie-details__skeleton movie-details__skeleton--trailer"
                ></div>
            </div>
        </div>

        <!-- Recommended Movies Section -->
        <div class="movie-details__recommendations-section">
            <div
                class="movie-details__skeleton movie-details__skeleton--section-title"
            ></div>
            <div class="movie-details__recommendations-grid">
                <div
                    class="movie-details__skeleton movie-details__skeleton--recommendation"
                    v-for="n in 6"
                    :key="n"
                ></div>
            </div>
        </div>
    </div>
</template>

<script setup>
// No script logic needed for skeleton
</script>

<style lang="scss" scoped>
:root {
    --details-bg: #ffffff;
    --details-border: #e5e7eb;
    --details-shadow: rgba(0, 0, 0, 0.1);
    --details-surface: #f9fafb;
    --details-accent: #3b82f6;
}

.movie-details {
    &__skeleton {
        background: linear-gradient(
            90deg,
            #1e293b 25%,
            #0f172a 50%,
            #1e293b 75%
        );
        background-size: 200% 100%;
        animation: skeleton-loading 1.2s infinite linear;
        border-radius: 8px;

        &--poster {
            width: 100%;
            aspect-ratio: 2/3;
            border-radius: 12px;
            background: var(--details-surface);
        }

        &--title {
            width: 70%;
            height: 2.5rem;
            border-radius: 6px;
        }

        &--genre {
            width: 80px;
            height: 1.75rem;
            border-radius: 20px;
            background: linear-gradient(
                90deg,
                var(--details-accent) 25%,
                #60a5fa 50%,
                var(--details-accent) 75%
            );
            background-size: 200% 100%;
            animation: skeleton-loading 1.2s infinite linear;
        }

        &--rating {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        &--star {
            width: 16px;
            height: 16px;
            border-radius: 50%;
        }

        &--rating-text {
            width: 30px;
            height: 1rem;
        }

        &--year,
        &--runtime {
            width: 60px;
            height: 1rem;
        }

        &--section-title {
            width: 180px;
            height: 1.5rem;
            margin-bottom: 1rem;
            border-radius: 6px;
        }

        &--plot-line {
            width: 100%;
            height: 1.1rem;
            margin-bottom: 0.5rem;
            border-radius: 4px;

            &.movie-details__skeleton--plot-line-short {
                width: 75%;
            }
        }

        &--info-item {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        &--info-label {
            width: 80px;
            height: 1rem;
            border-radius: 4px;
        }

        &--info-value {
            width: 120px;
            height: 1rem;
            border-radius: 4px;

            &.movie-details__skeleton--info-value-long {
                width: 200px;
            }
        }

        &--trailer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 12px;
        }

        &--recommendation {
            width: 100%;
            aspect-ratio: 2/3;
            border-radius: 12px;
            min-height: 270px;
        }
    }

    &__main {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 3rem;
        margin-bottom: 3rem;
        background: var(--details-bg);
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px -1px var(--details-shadow);
    }

    &__info {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    &__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: center;
    }

    &__additional-info {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    &__trailer-section {
        margin-bottom: 3rem;
    }

    &__trailer-container {
        position: relative;
        width: 100%;
        height: 0;
        padding-bottom: 56.25%;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px var(--details-shadow);
    }

    &__recommendations-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1.5rem;
    }
}

/* Responsive Design */
@media (max-width: 1024px) {
    .movie-details__main {
        grid-template-columns: 250px 1fr;
        gap: 2rem;
    }
    .movie-details__skeleton--title {
        height: 2rem;
    }
}

@media (max-width: 768px) {
    .movie-details__main {
        grid-template-columns: 1fr;
        gap: 1.5rem;
        padding: 1.5rem;
    }
    .movie-details__skeleton--poster {
        max-width: 300px;
        margin: 0 auto;
    }
    .movie-details__skeleton--title {
        height: 1.75rem;
    }
    .movie-details__recommendations-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
    }
    .movie-details__skeleton--recommendation {
        min-height: 225px;
    }
}

@media (max-width: 480px) {
    .movie-details__main {
        padding: 1.5rem;
    }
    .movie-details__skeleton--title {
        height: 1.5rem;
    }
    .movie-details__recommendations-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .movie-details__skeleton--recommendation {
        min-height: 200px;
    }
}

@keyframes skeleton-loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}
</style>
