import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/Home.vue'
import OportunidadesList from '../views/oportunidades/OportunidadesList.vue'
import OportunidadesPublicList from '../views/oportunidades/OportunidadesPublicList.vue'
import OportunidadeCreate from '../views/oportunidades/OportunidadeCreate.vue'
import OportunidadeEdit from '../views/oportunidades/OportunidadeEdit.vue'
import OportunidadePublic from '../views/oportunidades/OportunidadePublic.vue'
import OportunidadeShow from '../views/oportunidades/OportunidadeShow.vue'
import CandidaturasList from '../views/candidaturas/CandidaturasList.vue'
import CvAnalysis from '../views/CvAnalysis.vue'
import Reports from '../views/Reports.vue'

// Public views
import CompanyPublic from '../views/public/CompanyPublic.vue'
import CompaniesPublic from '../views/public/CompaniesPublic.vue'
import OportunidadesPublic from '../views/public/OportunidadesPublic.vue'

// Auth views
import Login from '../views/auth/Login.vue'

// Candidato views
import CandidatoRegister from '../views/candidatos/Register.vue'
import CandidatoLogin from '../views/candidatos/Login.vue'
import CandidatoProfile from '../views/candidatos/Profile.vue'
import CandidatoCandidaturas from '../views/candidatos/Candidaturas.vue'

// Company views
import CompanyRegister from '../views/empresas/Register.vue'
import CompanyLogin from '../views/empresas/Login.vue'
import CompanyDashboard from '../views/empresas/Dashboard.vue'
import Platform from '../views/Platform.vue'

// Auth service
import { authService } from '../services/auth'
import { companyAuthService } from '../services/companyAuth'
import { candidatoAuthService } from '../services/candidatoAuth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // Rotas públicas
    {
      path: '/',
      name: 'home',
      component: Home
    },
    {
      path: '/login',
      name: 'login',
      component: Login
    },
    
    // Rotas de candidatos
    {
      path: '/candidatos/register',
      name: 'candidatos.register',
      component: CandidatoRegister
    },
    {
      path: '/candidatos/login',
      name: 'candidatos.login',
      component: CandidatoLogin
    },
    {
      path: '/candidatos/profile',
      name: 'candidatos.profile',
      component: CandidatoProfile,
      meta: { requiresCandidatoAuth: true }
    },
    {
      path: '/candidatos/candidaturas',
      name: 'candidatos.candidaturas',
      component: CandidatoCandidaturas,
      meta: { requiresCandidatoAuth: true }
    },
    {
      path: '/candidatos/cv-optimizer',
      name: 'candidatos.cv-optimizer',
      component: () => import('../views/candidatos/CvOptimizer.vue'),
      meta: { requiresCandidatoAuth: true }
    },
    {
      path: '/candidatos/mensagens',
      name: 'candidatos.messages',
      component: () => import('../views/Messages.vue'),
      meta: { requiresCandidatoAuth: true }
    },

    // Rotas de colaborador (candidatos contratados)
    {
      path: '/colaborador',
      component: () => import('../views/colaborador/ColaboradorLayout.vue'),
      meta: { requiresCandidatoAuth: true, requiresColaboradorAccess: true },
      children: [
        {
          path: 'dashboard',
          name: 'colaborador.dashboard',
          component: () => import('../views/colaborador/ColaboradorDashboard.vue')
        },
        {
          path: 'contratos',
          name: 'colaborador.contratos',
          component: () => import('../views/colaborador/ColaboradorContratos.vue')
        },
        {
          path: 'perfil',
          name: 'colaborador.perfil',
          component: () => import('../views/colaborador/ColaboradorPerfil.vue')
        },
        {
          path: 'dias-nao-trabalhados',
          name: 'colaborador.dias-nao-trabalhados',
          component: () => import('../views/colaborador/ColaboradorDiasNaoTrabalhados.vue')
        },
        {
          path: 'mensagens',
          name: 'colaborador.mensagens',
          component: () => import('../views/colaborador/ColaboradorMensagens.vue')
        },
        {
          path: '',
          redirect: 'dashboard'
        }
      ]
    },
    {
      path: '/cv-analysis',
      name: 'cv.analysis',
      component: CvAnalysis
    },
    {
      path: '/oportunidades',
      name: 'oportunidades.public.all',
      component: OportunidadesPublic
    },
    {
      path: '/empresas',
      name: 'empresas.public',
      component: CompaniesPublic
    },
    {
      path: '/oportunidades-old',
      name: 'oportunidades.public',
      component: OportunidadesPublicList
    },
    {
      path: '/empresa/:slug',
      name: 'empresa.public',
      component: CompanyPublic,
      props: true
    },
    {
      path: '/oportunidade/:slug',
      name: 'oportunidade.public',
      component: OportunidadePublic,
      props: true
    },
    
    // Feedback de Recrutamento (rota pública)
    {
      path: '/feedback-recrutamento/:token',
      name: 'feedback.recrutamento',
      component: () => import('../views/FeedbackRecrutamento.vue'),
      props: true
    },
    
    // Rotas administrativas (protegidas)
    {
      path: '/admin',
      redirect: '/admin/oportunidades',
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/oportunidades',
      name: 'oportunidades.index',
      component: OportunidadesList,
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/oportunidades/create',
      name: 'oportunidades.create',
      component: OportunidadeCreate,
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/oportunidades/:slug/edit',
      name: 'oportunidades.edit',
      component: OportunidadeEdit,
      props: true,
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/oportunidades/:slug',
      name: 'oportunidades.show',
      component: OportunidadeShow,
      props: true,
      meta: { requiresAuth: true }
    },
    {
      path: '/candidaturas',
      name: 'candidaturas.index',
      component: CandidaturasList,
      meta: { requiresAuth: true }
    },
    {
      path: '/reports',
      name: 'reports',
      component: Reports,
      meta: { requiresAuth: true }
    },
    
    // Company routes
    {
      path: '/empresas/register',
      name: 'company.register',
      component: CompanyRegister
    },
    {
      path: '/empresas/login',
      name: 'company.login',
      component: CompanyLogin
    },
    {
      path: '/empresas/dashboard',
      name: 'company.dashboard',
      component: CompanyDashboard,
      meta: { requiresAuth: true }
    },
    {
      path: '/empresas/oportunidades/:id/candidatos',
      name: 'company.candidates',
      component: () => import('../views/empresas/Candidates.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/empresas/oportunidades/:id/kanban',
      name: 'company.kanban',
      component: () => import('../views/empresas/KanbanBoard.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/empresas/oportunidades/:id/candidatos-potencial',
      name: 'company.candidatosPotencial',
      component: () => import('../views/empresas/CandidatosPotencial.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/empresas/oportunidades/:id/relatorios',
      name: 'company.reports',
      component: () => import('../views/empresas/Reports.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/empresas/oportunidades/:id/colaboradores',
      name: 'company.oportunidade.colaboradores',
      component: () => import('../views/empresas/OportunidadeColaboradores.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/empresas/oportunidades/:id/feedbacks',
      name: 'company.oportunidade.feedbacks',
      component: () => import('../views/empresas/OportunidadeFeedbacks.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/empresas/oportunidades/:id/mensagens',
      name: 'company.oportunidade.mensagens',
      component: () => import('../views/empresas/OportunidadeMensagens.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/empresas/perfil',
      name: 'company.profile',
      component: () => import('../views/empresas/Profile.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/empresas/colaboradores',
      name: 'company.colaboradores',
      component: () => import('../views/empresas/Colaboradores.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/empresas/dias-nao-trabalhados',
      name: 'company.dias-nao-trabalhados',
      component: () => import('../views/empresas/DiasNaoTrabalhados.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/empresas/candidatos-online-map',
      name: 'company.candidatos-online-map',
      component: () => import('../views/empresas/OnlineCandidatesMap.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/empresas/mensagens',
      name: 'company.messages',
      component: () => import('../views/Messages.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/platform',
      name: 'platform',
      component: Platform
    }
  ]
})

// Guard de autenticação
router.beforeEach(async (to, from, next) => {
  if (to.meta.requiresAuth) {
    // Verificar se é uma rota de empresa
    const isCompanyRoute = to.path.startsWith('/empresas/') && to.path !== '/empresas/login' && to.path !== '/empresas/register'
    
    let isAuthenticated = false
    
    if (isCompanyRoute) {
      // Usar autenticação de empresa
      isAuthenticated = companyAuthService.isAuthenticated()
      if (isAuthenticated) {
        try {
          await companyAuthService.me()
        } catch (error) {
          isAuthenticated = false
          companyAuthService.logout()
        }
      }
      
      if (!isAuthenticated) {
        next('/empresas/login')
        return
      }
    } else {
      // Usar autenticação normal
      isAuthenticated = await authService.checkAuth()
      
      if (!isAuthenticated) {
        next('/login')
        return
      }
    }
  }
  
  // Guard específico para candidatos
  if (to.meta.requiresCandidatoAuth) {
    const isAuthenticated = candidatoAuthService.isAuthenticated()
    
    if (!isAuthenticated) {
      next('/candidatos/login')
      return
    }
    
    try {
      await candidatoAuthService.me()
    } catch (error) {
      candidatoAuthService.logout()
      next('/candidatos/login')
      return
    }
  }

  // Guard específico para colaborador (candidatos contratados)
  if (to.meta.requiresColaboradorAccess) {
    try {
      // Importar colaboradorService dinamicamente para evitar dependência circular
      const colaboradorService = (await import('../services/colaboradorService')).default
      const acessoResult = await colaboradorService.verificarAcesso()
      
      if (!acessoResult.is_colaborador) {
        // Redirecionar para área do candidato com uma mensagem
        next('/candidatos/profile?message=colaborador_access_denied')
        return
      }
    } catch (error) {
      console.error('Erro ao verificar acesso de colaborador:', error)
      next('/candidatos/login')
      return
    }
  }
  
  next()
})

export default router
