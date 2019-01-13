<template>
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-3">
                <div class="form-row">
                    <div class="col-md-6 mb-3">
                        <label for="url" class="sr-only">URL</label>
                        <input type="text" class="form-control" v-model="url" id="url"
                               placeholder="Enter URL" required>
                        <small class="form-text text-muted" v-if="validateError">
                            {{validateError}}
                        </small>
                        <small class="form-text text-muted" v-if="processing">
                            Processing... it may take a while, please wait
                        </small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <button type="button" @click="submit" :disabled="processing" class="btn btn-primary mb-2">
                            Submit
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" v-if="commentsOrReviews">
            <div class="col-md-4 offset-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Your download is ready</h5>
                        <vue-json-to-csv
                            :json-data=commentsOrReviews
                            :labels="{
                                username: { title: 'User Name' },
                                date: { title: 'Date' },
                                starRating: { title: 'Star Rating' },
                                comment: { title: 'Review or Comment' },
                                link: { title: 'Link' },
                            }"
                            :csv-title="csvTitle"
                            @success="val => handleSuccess(val)"
                            @error="val => handleError(val)">
                            <button type="button" class="btn btn-primary">Download CSV</button>
                        </vue-json-to-csv>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" v-if="alert">
            <div class="col-md-4 offset-3">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Thank You!</strong> for downloading...
                    <button type="button" class="close" @click="close" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import VueJsonToCsv from 'vue-json-to-csv'

    export default {
        data() {
            return {
                url: '',
                validateError: false,
                processing: false,
                commentsOrReviews: false,
                csvTitle: 'Youtube video comments or Amazon product reviews',
                alert: false
            }
        },
        components: {VueJsonToCsv},
        mounted() {
            // console.log('Component mounted.')
        },
        watch: {
            url(value) {
                if (value !== '') {
                    this.validateError = false
                    this.commentsOrReviews = false
                    this.alert = false
                }
            }
        },
        methods: {
            async submit() {
                this.validateError = false
                this.processing = true
                if (this.url !== '') {
                    try {
                        const {data} = await axios.post(`/reviews`, {url: this.url})
                        this.url = ''
                        if (typeof(data) === 'object') {
                            this.commentsOrReviews = data
                        } else {
                            console.log(data)
                        }
                    } catch (e) {
                        console.log(e.message)
                    }
                } else {
                    this.validateError = 'The URL field is required'
                }
                this.processing = false
            },
            handleSuccess(val) {
                if (val) {
                    this.commentsOrReviews = false
                    this.alert = true
                }
            },
            handleError(val) {
                console.log(val)
            },
            close() {
                this.alert = false
            }
        }
    }
</script>
