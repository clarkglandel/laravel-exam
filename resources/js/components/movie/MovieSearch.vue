<template>
    <div class="movie-search">
        <div class="movie-search__box">
            <div class="movie-search__input-wrapper">
                <svg
                    class="movie-search__icon"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                >
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <input
                    v-model="query"
                    type="text"
                    placeholder="Search for movies, TV shows..."
                    class="movie-search__input"
                    @keyup.enter="handleSubmit"
                    @input="handleInput"
                />
                <button
                    v-if="query"
                    @click="handleClear"
                    class="movie-search__clear-button"
                    type="button"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <button
                class="movie-search__button"
                type="button"
                @click="handleSubmit"
                :disabled="props.loading || !query.trim()"
            >
                <div
                    v-if="props.loading"
                    class="movie-search__loading-spinner"
                ></div>
                <svg v-else viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"
                    />
                </svg>
                Search
            </button>
        </div>

        <ErrorMessage v-if="error" :message="error" />
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import ErrorMessage from "@/components/common/ErrorMessage.vue";

const emit = defineEmits(["search"]);
const props = defineProps({
    loading: Boolean,
    title: String,
});

const query = ref(props.title || "");
const error = ref("");

// Keep input in sync with prop changes
watch(
    () => props.title,
    (newTitle) => {
        if (typeof newTitle === "string") {
            query.value = newTitle;
        }
    }
);

function handleInput() {
    error.value = "";
}

async function handleSubmit() {
    if (!query.value.trim()) return;
    emit("search", query.value);
}

function handleClear() {
    query.value = "";
    emit("search", "");
}
</script>

<style lang="scss" scoped>
:root {
    --input-bg: #ffffff;
    --input-border: #e5e7eb;
    --input-text: #111827;
    --input-placeholder: #9ca3af;
    --button-primary: #3b82f6;
    --button-primary-hover: #2563eb;
    --clear-hover-bg: #f3f4f6;
}

:root.dark-theme {
    --input-bg: #1f2937;
    --input-border: #374151;
    --input-text: #f9fafb;
    --input-placeholder: #6b7280;
    --button-primary: #3b82f6;
    --button-primary-hover: #2563eb;
    --clear-hover-bg: #374151;
}

.movie-search {
    max-width: 600px;
    margin: 0 auto;
    padding: 0 1rem;

    &__box {
        display: flex;
        gap: 1rem;
        align-items: stretch;
        margin-bottom: 1rem;
    }

    &__input-wrapper {
        position: relative;
        flex: 1;
        display: flex;
        align-items: center;
    }

    &__icon {
        position: absolute;
        left: 1rem;
        width: 20px;
        height: 20px;
        color: var(--input-placeholder);
        z-index: 1;
    }

    &__input {
        width: 100%;
        padding: 1rem 1rem 1rem 3rem;
        font-size: 1rem;
        border: 2px solid var(--input-border);
        border-radius: 16px;
        background: var(--input-bg);
        color: var(--input-text);
        transition: all 0.2s ease;
        outline: none;

        &:focus {
            border-color: var(--button-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        &::placeholder {
            color: var(--input-placeholder);
        }
    }

    &__clear-button {
        position: absolute;
        right: 1rem;
        width: 24px;
        height: 24px;
        border: none;
        background: none;
        color: var(--input-placeholder);
        cursor: pointer;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;

        &:hover {
            color: var(--input-text);
            background: var(--clear-hover-bg);
        }

        svg {
            width: 16px;
            height: 16px;
        }
    }

    &__button {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem 2rem;
        background: var(--button-primary);
        color: white;
        border: none;
        border-radius: 16px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;

        &:hover:not(:disabled) {
            background: var(--button-primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        &:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        svg {
            width: 18px;
            height: 18px;
        }
    }

    &__loading-spinner {
        width: 18px;
        height: 18px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top: 2px solid white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

// Responsive Design
@media (max-width: 640px) {
    .movie-search {
        &__box {
            flex-direction: column;
            gap: 0.75rem;
        }

        &__button {
            justify-content: center;
            padding: 1rem;
        }

        padding: 0 0.5rem;
    }
}
</style>
