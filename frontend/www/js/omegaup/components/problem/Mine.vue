<template>
  <div>
    <div
      class="alert alert-info alert-dismissible fade show"
      v-if="privateProblemsAlert"
      role="alert"
    >
      {{ T.messageMakeYourProblemsPublic }}
      <button
        type="button"
        class="close"
        data-dismiss="alert"
        aria-label="Close"
      >
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="card">
      <h5 class="card-header">{{ T.myproblemsListMyProblems }}</h5>
      <div class="card-body">
        <div class="row align-items-center justify-content-between">
          <div class="form-check col-7">
            <label class="form-check-label">
              <input
                class="form-check-input"
                type="checkbox"
                v-model="shouldShowAllProblems"
                v-on:change.prevent="
                  $emit('change-show-all-problems', shouldShowAllProblems)
                "
              />
              <span>{{ statementShowAllProblems }}</span>
            </label>
          </div>
          <select
            class="custom-select col-5"
            v-model="allProblemsVisibilityOption"
            v-on:change="onChangeVisibility"
          >
            <option selected value="-1">{{ T.forSelectedItems }}</option>
            <option value="1">{{ T.makePublic }}</option>
            <option value="0">{{ T.makePrivate }}</option>
          </select>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead>
            <tr>
              <th scope="col" class="text-center"></th>
              <th scope="col" class="text-center">{{ T.wordsID }}</th>
              <th scope="col" class="text-center">{{ T.wordsTitle }}</th>
              <th scope="col" class="text-center">{{ T.wordsEdit }}</th>
              <th scope="col" class="text-center">{{ T.wordsStatistics }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="problem in problems">
              <td class="align-middle">
                <input
                  type="checkbox"
                  v-model="selectedProblems"
                  v-bind:disabled="problem.visibility === -10"
                  v-bind:value="problem.alias"
                />
              </td>
              <td class="text-right align-middle">
                {{ problem.problem_id }}
              </td>
              <td class="d-flex align-items-center">
                <div class="d-inline-block ml-2">
                  <a
                    class="mr-1"
                    v-bind:href="`/arena/problem/${problem.alias}/`"
                    >{{ problem.title }}</a
                  >
                  <font-awesome-icon
                    v-bind:title="T.wordsPrivate"
                    v-if="problem.visibility <= 0 && problem.visibility > -10"
                    v-bind:icon="['fas', 'eye-slash']"
                  />
                  <font-awesome-icon
                    v-bind:title="T.wordsDeleted"
                    v-else-if="problem.visibility === -10"
                    v-bind:icon="['fas', 'trash']"
                  />
                  <div class="tags-badges" v-if="problem.tags.length">
                    <a
                      v-bind:class="
                        `badge custom-badge custom-badge-${tag.source} m-1 p-2`
                      "
                      v-bind:href="`/problem/?tag[]=${tag.name}`"
                      v-for="tag in problem.tags"
                      >{{
                        T.hasOwnProperty(tag.name) ? T[tag.name] : tag.name
                      }}</a
                    >
                  </div>
                </div>
              </td>
              <td class="text-center align-middle">
                <a v-bind:href="`/problem/${problem.alias}/edit/`">
                  <font-awesome-icon v-bind:icon="['fas', 'edit']" />
                </a>
              </td>
              <td class="text-center align-middle">
                <a v-bind:href="`/problem/${problem.alias}/stats/`">
                  <font-awesome-icon v-bind:icon="['fas', 'chart-bar']" />
                </a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="card-footer">
        <omegaup-common-paginator
          v-bind:pagerItems="pagerItems"
          v-on:page-changed="page => $emit('go-to-page', page)"
        ></omegaup-common-paginator>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import common_Paginator from '../common/Paginatorv2.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faEyeSlash,
  faTrash,
  faEdit,
  faChartBar,
} from '@fortawesome/free-solid-svg-icons';
library.add(faEyeSlash, faTrash, faEdit, faChartBar);

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-common-paginator': common_Paginator,
  },
})
export default class ProblemMine extends Vue {
  @Prop() problems!: types.ProblemListItem[];
  @Prop() pagerItems!: types.PageItem[];
  @Prop() privateProblemsAlert!: boolean;
  @Prop() isSysadmin!: boolean;

  T = T;
  shouldShowAllProblems = false;
  selectedProblems = [];
  allProblemsVisibilityOption = -1;

  get statementShowAllProblems(): string {
    return this.isSysadmin
      ? T.problemListShowAdminProblemsAndDeleted
      : T.problemListShowAdminProblems;
  }

  onChangeVisibility(): void {
    if (
      this.allProblemsVisibilityOption !== -1 &&
      this.selectedProblems.length
    ) {
      this.$emit(
        'change-visibility',
        this.selectedProblems,
        this.allProblemsVisibilityOption,
      );
      this.selectedProblems = [];
      this.allProblemsVisibilityOption = -1;
    }
  }
}
</script>
