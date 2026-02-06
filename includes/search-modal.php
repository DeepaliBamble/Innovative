<!-- Search Modal -->
<div class="modal fade" id="search" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
            <div class="modal-header" style="border-bottom: 2px solid #EDEDED; padding: 24px 32px;">
                <h5 class="modal-title" id="searchModalLabel" style="color: #9e6747; font-weight: 600; font-size: 1.5rem;">
                    <i class="icon icon-magnifying-glass" style="margin-right: 10px;"></i>
                    Search Products
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 32px;">
                <form action="search.php" method="GET" id="searchForm">
                    <div class="search-box" style="position: relative;">
                        <input
                            type="text"
                            name="q"
                            id="searchInput"
                            class="form-control"
                            placeholder="Search for furniture, categories, or products..."
                            autocomplete="off"
                            style="
                                width: 100%;
                                padding: 16px 60px 16px 20px;
                                border: 2px solid #EDEDED;
                                border-radius: 12px;
                                font-size: 1rem;
                                transition: all 0.3s ease;
                                background: #faf1e5;
                            "
                            required
                        >
                        <button
                            type="submit"
                            class="search-submit-btn"
                            style="
                                position: absolute;
                                right: 8px;
                                top: 50%;
                                transform: translateY(-50%);
                                background: linear-gradient(135deg, #9e6747 0%, #d89d43 100%);
                                color: white;
                                border: none;
                                padding: 10px 20px;
                                border-radius: 8px;
                                cursor: pointer;
                                transition: all 0.3s ease;
                                font-weight: 600;
                            "
                            onmouseover="this.style.transform='translateY(-50%) scale(1.05)'"
                            onmouseout="this.style.transform='translateY(-50%) scale(1)'"
                        >
                            <i class="icon icon-magnifying-glass"></i> Search
                        </button>
                    </div>
                </form>

                <!-- Search Tips -->
                <div class="search-tips" style="margin-top: 24px; padding: 16px; background: #f8f9fa; border-radius: 8px;">
                    <p style="color: #8A8C8A; font-size: 0.85rem; margin: 0;">
                        <i class="icon icon-info" style="margin-right: 6px;"></i>
                        <strong>Tip:</strong> Try searching by product name, category, or style (e.g., "L-shape sofa", "accent chair", "modern dining table")
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #searchInput:focus {
        outline: none;
        border-color: #9e6747;
        box-shadow: 0 0 0 4px rgba(158, 103, 71, 0.1);
    }

    .search-submit-btn:hover {
        box-shadow: 0 4px 12px rgba(158, 103, 71, 0.3);
    }

    .modal-backdrop.show {
        opacity: 0.7;
    }

    @media (max-width: 576px) {
        .modal-dialog {
            margin: 1rem;
        }

        .modal-body {
            padding: 20px !important;
        }

        #searchInput {
            padding: 14px 50px 14px 16px !important;
            font-size: 0.95rem !important;
        }

        .popular-tag {
            font-size: 0.85rem !important;
        }
    }
</style>

<script>
    // Auto-focus search input when modal opens
    document.getElementById('search').addEventListener('shown.bs.modal', function () {
        document.getElementById('searchInput').focus();
    });

    // Submit form on Enter key
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('searchForm').submit();
        }
    });
</script>
