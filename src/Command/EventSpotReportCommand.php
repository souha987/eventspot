<?php

namespace App\Command;

use App\Repository\EvenementRepository;
use App\Repository\InscriptionRepository;
use App\Repository\LieuRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:eventspot:report',
    description: 'Génère un rapport sur les événements et inscriptions',
)]
class EventSpotReportCommand extends Command
{
    public function __construct(
        private EvenementRepository $evenementRepo,
        private InscriptionRepository $inscriptionRepo,
        private LieuRepository $lieuRepo,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('upcoming', null, InputOption::VALUE_NONE, 'Afficher uniquement les événements à venir')
            ->addOption('lieu', null, InputOption::VALUE_REQUIRED, 'Filtrer par nom de lieu');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('📊 Rapport EventSpot');

        $upcoming = $input->getOption('upcoming');
        $lieuNom  = $input->getOption('lieu');

        // Récupérer tous les événements
        $qb = $this->evenementRepo->createQueryBuilder('e')
            ->leftJoin('e.lieu', 'l')
            ->leftJoin('e.inscriptions', 'i')
            ->addSelect('l');

        if ($upcoming) {
            $qb->andWhere('e.dateDebut >= :now')
               ->setParameter('now', new \DateTime());
        }

        if ($lieuNom) {
            $qb->andWhere('l.nom LIKE :lieu')
               ->setParameter('lieu', '%' . $lieuNom . '%');
        }

        $evenements = $qb->getQuery()->getResult();

        if (empty($evenements)) {
            $io->warning('Aucun événement trouvé.');
            return Command::SUCCESS;
        }

        // --- Statuts événements ---
        $statutsEv = [];
        foreach ($evenements as $e) {
            $s = $e->getStatut();
            $statutsEv[$s] = ($statutsEv[$s] ?? 0) + 1;
        }

        $io->section('Événements par statut');
        $rows = [];
        foreach ($statutsEv as $statut => $count) {
            $rows[] = [$statut, $count];
        }
        $io->table(['Statut', 'Nombre'], $rows);

        // --- Statuts inscriptions ---
        $io->section('Inscriptions par statut');
        $tousStatuts = ['confirmee', 'en_attente', 'annulee'];
        $statutsInsc = [];
        foreach ($tousStatuts as $s) {
            $statutsInsc[$s] = $this->inscriptionRepo->count(['statut' => $s]);
        }
        $rowsInsc = [];
        foreach ($statutsInsc as $statut => $count) {
            $rowsInsc[] = [$statut, $count];
        }
        $io->table(['Statut', 'Nombre'], $rowsInsc);

        // --- Taux de remplissage moyen ---
        $io->section('Taux de remplissage moyen');
        $tauxTotal = 0;
        $count = 0;
        foreach ($evenements as $e) {
            if ($e->getCapaciteMax() > 0) {
                $nbInscrits = $this->inscriptionRepo->count([
                    'evenement' => $e,
                    'statut' => 'confirmee'
                ]);
                $tauxTotal += ($nbInscrits / $e->getCapaciteMax()) * 100;
                $count++;
            }
        }
        $tauxMoyen = $count > 0 ? round($tauxTotal / $count, 2) : 0;
        $io->note("Taux de remplissage moyen : {$tauxMoyen}%");

        // --- Répartition par catégorie ---
        $io->section('Répartition par catégorie');
        $categories = [];
        foreach ($evenements as $e) {
            $cat = $e->getCategorie();
            $categories[$cat] = ($categories[$cat] ?? 0) + 1;
        }
        $rowsCat = [];
        foreach ($categories as $cat => $nb) {
            $rowsCat[] = [$cat, $nb];
        }
        $io->table(['Catégorie', 'Nombre'], $rowsCat);

        // --- Top 3 événements ---
        $io->section('Top 3 événements les plus populaires');
        $popularite = [];
        foreach ($evenements as $e) {
            $nb = $this->inscriptionRepo->count([
                'evenement' => $e,
                'statut' => 'confirmee'
            ]);
            $popularite[] = ['evenement' => $e, 'nb' => $nb];
        }
        usort($popularite, fn($a, $b) => $b['nb'] - $a['nb']);
        $top3 = array_slice($popularite, 0, 3);
        $rowsTop = [];
        foreach ($top3 as $item) {
            $rowsTop[] = [$item['evenement']->getTitre(), $item['nb'] . ' inscrits'];
        }
        $io->table(['Événement', 'Inscrits confirmés'], $rowsTop);

        // --- Revenu total estimé ---
        $io->section('Revenu total estimé');
        $revenu = 0;
        foreach ($evenements as $e) {
            if ($e->getPrix() > 0) {
                $nb = $this->inscriptionRepo->count([
                    'evenement' => $e,
                    'statut' => 'confirmee'
                ]);
                $revenu += $e->getPrix() * $nb;
            }
        }
        $io->note('Revenu total estimé : ' . number_format($revenu, 2, ',', ' ') . ' €');

        $io->success('Rapport généré avec succès !');

        return Command::SUCCESS;
    }
}